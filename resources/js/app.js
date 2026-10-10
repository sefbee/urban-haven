import Alpine from 'alpinejs';
import { track, bootAnalytics, bindTrackedClicks } from './analytics';
import { applySelectOption, bootAdminSelects } from './admin-selects';
import { registerAdminForms } from './admin-forms';
import { bootPublicSelects } from './public-selects';
import { registerSavedStore } from './saved';
import { bootMaps, refreshMapData, refreshMaps } from './maps';

/**
 * Trap keyboard focus within a container element.
 * Call with the container element; returns a handler to attach to keydown.tab.
 * @param {HTMLElement} container
 * @param {KeyboardEvent} event
 */
const trapFocus = (container, event) => {
    const focusable = Array.from(
        container.querySelectorAll(
            'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
        ),
    ).filter((el) => !el.closest('[hidden]') && getComputedStyle(el).display !== 'none');

    if (focusable.length === 0) {
        return;
    }

    const first = focusable[0];
    const last = focusable[focusable.length - 1];

    if (event.shiftKey) {
        if (document.activeElement === first) {
            event.preventDefault();
            last.focus();
        }
    } else {
        if (document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    }
};

const syncAdminSheets = (name) => {
    document.querySelectorAll('.uh-admin-sheet').forEach((sheet) => {
        sheet.classList.toggle('is-open', Boolean(name) && sheet.dataset.drawer === name);
    });
};

const prefersReducedMotion = () =>
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const escapeHtml = (value) =>
    String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

/**
 * Public header. Transparent over a full-bleed cover, then a solid surface once the cover has
 * scrolled away. It never hides, so navigation is always one glance away.
 */
Alpine.data('uhSiteBar', (overlay = false) => ({
    open: false,
    lang: false,
    overlay,
    solid: !overlay,
    ticking: false,
    init() {
        this.update();
        window.addEventListener('scroll', () => this.queue(), { passive: true });
        window.addEventListener('resize', () => this.queue(), { passive: true });
    },
    queue() {
        if (this.ticking) {
            return;
        }
        this.ticking = true;
        requestAnimationFrame(() => {
            this.ticking = false;
            this.update();
        });
    },
    threshold() {
        const cover = document.querySelector('[data-cover]');

        return cover ? Math.max(24, cover.offsetHeight - this.$el.offsetHeight) : 24;
    },
    update() {
        this.solid = !this.overlay || window.scrollY > this.threshold();
    },
}));

/**
 * Homepage cover photos crossfade on a timer. Choosing a photo, a hidden tab or reduced motion stops the timer.
 */
Alpine.data('uhCoverSlides', (count = 0, seconds = 6) => ({
    count,
    active: 0,
    timer: null,
    init() {
        if (this.count < 2 || prefersReducedMotion()) {
            return;
        }
        this.timer = window.setInterval(() => {
            if (!document.hidden) {
                this.active = (this.active + 1) % this.count;
            }
        }, Math.max(3, Math.min(20, seconds)) * 1000);
    },
    show(index) {
        this.active = index;
        this.destroy();
    },
    destroy() {
        window.clearInterval(this.timer);
        this.timer = null;
    },
}));

/**
 * Image gallery with keyboard navigation and an optional lightbox.
 */
Alpine.data('uhGallery', (count = 0) => ({
    count,
    active: 0,
    lightbox: false,
    _opener: null,
    select(index) {
        this.active = Math.max(0, Math.min(index, this.count - 1));
    },
    next() {
        this.active = (this.active + 1) % this.count;
    },
    previous() {
        this.active = (this.active - 1 + this.count) % this.count;
    },
    open(index = null) {
        if (index !== null) {
            if (index < 0 || index >= this.count) {
                console.warn(`uhGallery.open: index ${index} is out of bounds [0, ${this.count})`);
                return;
            }
            this.active = index;
        }
        this._opener = document.activeElement;
        this.lightbox = true;
        document.body.classList.add('overflow-hidden');
        this.$nextTick(() => this.$el.focus());
    },
    close() {
        this.lightbox = false;
        document.body.classList.remove('overflow-hidden');
        this._opener?.focus();
    },
}));

/**
 * Listing browse: filter drawer, list or card layout, and quick view.
 */
Alpine.data('uhBrowse', (hasAdvanced = false) => ({
    filtersOpen: false,
    submitting: false,
    more: hasAdvanced,
    layout: 'grid',
    mapMode: 'split',
    preview: null,
    slide: 0,
    requestController: null,
    init() {
        try {
            const stored = localStorage.getItem('uh-browse-layout');
            if (stored === 'grid' || stored === 'list') {
                this.layout = stored;
            }
        } catch {
            this.layout = 'grid';
        }
    },
    async search(form) {
        if (!form) {
            return;
        }
        this.requestController?.abort();
        const controller = new AbortController();
        this.requestController = controller;
        this.submitting = true;
        const url = new URL(form.action, window.location.origin);
        url.search = new URLSearchParams(new FormData(form)).toString();
        try {
            const response = await fetch(url, { signal: controller.signal, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) {
                throw new Error('Search request failed');
            }
            const documentResult = new DOMParser().parseFromString(await response.text(), 'text/html');
            const selectors = document.querySelector('.uh-mapview-results')
                ? ['.uh-mapview-results']
                : ['.uh-results-bar', '.uh-active-filters', '.uh-workspace'];
            selectors.forEach((selector) => {
                const current = document.querySelector(selector);
                const replacement = documentResult.querySelector(selector);
                if (current && replacement) {
                    current.replaceWith(replacement);
                    Alpine.initTree(replacement);
                } else if (current && !replacement) {
                    current.remove();
                } else if (!current && replacement && selector === '.uh-active-filters') {
                    document.querySelector('.uh-results-bar')?.after(replacement);
                    Alpine.initTree(replacement);
                }
            });
            window.history.pushState({}, '', url);
            const mapLink = form.querySelector('.uh-filter-map');
            if (mapLink) {
                const mapUrl = new URL(mapLink.href, window.location.origin);
                mapUrl.search = url.search;
                mapLink.href = mapUrl;
            }

            const mapElement = document.querySelector('[data-uh-map]');
            if (mapElement?.dataset.src) {
                const dataUrl = new URL(mapElement.dataset.src, window.location.origin);
                dataUrl.search = url.search;
                mapElement.dataset.src = dataUrl.href;
                window.dispatchEvent(new Event('uh:refresh-map-data'));
            }
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }
            window.location.assign(url);
        } finally {
            if (this.requestController === controller) {
                this.submitting = false;
                this.requestController = null;
            }
        }
    },
    setLayout(layout) {
        this.layout = layout;
        try {
            localStorage.setItem('uh-browse-layout', layout);
        } catch {
            // Private browsing can block storage; the choice still applies this visit.
        }
    },
    toggleMapMode() {
        if (this.mapMode === 'full') {
            const listUrl = new URL(this.$el.dataset.listUrl, window.location.origin);
            listUrl.search = window.location.search;
            listUrl.searchParams.delete('page');
            window.location.assign(listUrl);

            return;
        }

        this.mapMode = 'full';
        this.$nextTick(() => {
            window.dispatchEvent(new Event('uh:refresh-maps'));
            window.setTimeout(() => window.dispatchEvent(new Event('uh:refresh-maps')), 300);
        });
    },
    toggleFilters() {
        this.filtersOpen = !this.filtersOpen;
        if (this.filtersOpen) {
            this.more = true;
        }
        this.lockBody();
    },
    closeFilters() {
        this.filtersOpen = false;
        this.lockBody();
    },
    openPreview(payload) {
        this.slide = 0;
        this.preview = payload;
        this.lockBody();
        this.$nextTick(() => this.$refs.previewClose?.focus());
    },
    closePreview() {
        this.preview = null;
        this.slide = 0;
        this.lockBody();
    },
    onEscape() {
        if (this.preview) {
            this.closePreview();
            return;
        }
        this.closeFilters();
    },
    nextSlide() {
        const total = this.preview?.images?.length ?? 0;
        if (total > 1) {
            this.slide = (this.slide + 1) % total;
        }
    },
    previousSlide() {
        const total = this.preview?.images?.length ?? 0;
        if (total > 1) {
            this.slide = (this.slide - 1 + total) % total;
        }
    },
    lockBody() {
        const drawerOpen = this.filtersOpen;
        document.body.style.overflow = drawerOpen || this.preview ? 'hidden' : '';
    },
}));

/**
 * Save and revisit property searches on this device.
 */
Alpine.data('uhSavedSearch', (url, title, labels) => ({
    items: [],
    notice: '',
    open: false,
    noticeTimer: null,
    labels,
    init() {
        try {
            const stored = JSON.parse(localStorage.getItem('uh:v1:saved-searches') || '[]');
            this.items = Array.isArray(stored)
                ? stored.filter((item) => {
                    if (!item || typeof item.url !== 'string' || typeof item.title !== 'string') {
                        return false;
                    }

                    try {
                        return new URL(item.url, window.location.origin).origin === window.location.origin;
                    } catch {
                        return false;
                    }
                }).slice(0, 20)
                : [];
        } catch {
            this.items = [];
        }
    },
    get saved() {
        return this.items.some((item) => item.url === url);
    },
    toggle() {
        if (this.saved) {
            this.remove(url);
        } else {
            this.items = [{ url, title }, ...this.items].slice(0, 20);
            this.notice = this.labels.saved;
            this.persist();
        }

        this.open = true;
        window.clearTimeout(this.noticeTimer);
        this.noticeTimer = window.setTimeout(() => this.open = false, 2400);
    },
    remove(searchUrl) {
        this.items = this.items.filter((item) => item.url !== searchUrl);
        this.notice = this.labels.removed;
        this.persist();
    },
    persist() {
        try {
            localStorage.setItem('uh:v1:saved-searches', JSON.stringify(this.items));
        } catch {
            this.notice = this.labels.storageError;
        }
    },
}));

/**
 * Horizontal card rail with previous/next controls.
 */
Alpine.data('uhRail', () => ({
    canPrev: false,
    canNext: false,
    init() {
        this.update();
        this.$refs.scroller?.addEventListener('scroll', () => this.update(), { passive: true });
        window.addEventListener('resize', () => this.update(), { passive: true });
    },
    update() {
        const scroller = this.$refs.scroller;
        if (!scroller) {
            return;
        }

        this.canPrev = scroller.scrollLeft > 8;
        this.canNext = scroller.scrollLeft + scroller.clientWidth < scroller.scrollWidth - 8;
    },
    prev() {
        this.scrollBy(-1);
    },
    next() {
        this.scrollBy(1);
    },
    scrollBy(direction) {
        const scroller = this.$refs.scroller;
        if (!scroller) {
            return;
        }

        scroller.scrollBy({
            left: direction * Math.max(280, scroller.clientWidth * 0.8),
            behavior: prefersReducedMotion() ? 'auto' : 'smooth',
        });
    },
}));

/**
 * Sticky in-page tabs that follow the section being read.
 */
Alpine.data('uhSectionTabs', () => ({
    current: null,
    sections: [],
    ticking: false,
    lockedUntil: 0,
    onScroll: null,
    onClick: null,
    init() {
        this.sections = [...this.$el.querySelectorAll('a[href^="#"]')]
            .map((link) => document.getElementById(link.getAttribute('href').slice(1)))
            .filter(Boolean);
        this.current = this.sections[0]?.id ?? null;
        this.onScroll = () => {
            if (this.ticking) {
                return;
            }
            this.ticking = true;
            requestAnimationFrame(() => {
                this.ticking = false;
                this.update();
            });
        };
        this.onClick = (event) => {
            const link = event.target.closest('a[href^="#"]');
            if (!link) {
                return;
            }
            this.current = link.getAttribute('href').slice(1);
            this.lockedUntil = Date.now() + 900;
            this.reveal();
        };
        window.addEventListener('scroll', this.onScroll, { passive: true });
        this.$el.addEventListener('click', this.onClick);
        this.update();
    },
    destroy() {
        window.removeEventListener('scroll', this.onScroll);
        this.$el.removeEventListener('click', this.onClick);
    },
    update() {
        if (Date.now() < this.lockedUntil) {
            return;
        }

        const line = this.$el.getBoundingClientRect().bottom + Math.max(120, window.innerHeight * 0.2);
        const passed = this.sections.filter((section) =>
            getComputedStyle(section).position !== 'sticky' && section.getBoundingClientRect().top <= line);
        const atEnd = window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 4;
        const next = atEnd ? this.sections.at(-1) : (passed.at(-1) ?? this.sections[0]);

        if (next && next.id !== this.current) {
            this.current = next.id;
            this.reveal();
        }
    },
    reveal() {
        this.$nextTick(() => {
            const link = this.$el.querySelector(`a[href="#${this.current}"]`);
            const strip = link?.parentElement;
            if (link && strip && strip.scrollWidth > strip.clientWidth) {
                strip.scrollTo({ left: link.offsetLeft - 16, behavior: prefersReducedMotion() ? 'auto' : 'smooth' });
            }
        });
    },
}));

/**
 * Homepage sale/rent section tabs.
 */
Alpine.data('uhHomeTabs', (initial = 'sale') => ({
    tab: initial,
}));

/**
 * Homepage featured carousel: one centred listing with its neighbours turned away in 3D.
 * Wraps in both directions; swipe, arrow keys and clicking a neighbour all move it. Hovering a
 * listing previews its second photo when available; selection changes only through user input.
 */
Alpine.data('uhShowcase', (count = 0) => ({
    count,
    active: 0,
    hovered: null,
    dragging: false,
    dragX: 0,
    photoOn(index, photoIndex, total) {
        return photoIndex === (this.hovered === index && total > 1 ? 1 : 0);
    },
    hoverCard(index, total) {
        this.hovered = total > 1 ? index : null;
    },
    leaveCard(index) {
        if (this.hovered === index) {
            this.hovered = null;
        }
    },
    slot(index) {
        if (this.count < 2) {
            return 'active';
        }

        let offset = (index - this.active + this.count) % this.count;
        if (offset > this.count / 2) {
            offset -= this.count;
        }

        if (offset === 0) {
            return 'active';
        }
        if (offset === 1) {
            return 'next';
        }
        if (offset === -1) {
            return 'prev';
        }

        return offset > 0 ? 'far-next' : 'far-prev';
    },
    go(index) {
        this.active = (index + this.count) % this.count;
        this.hovered = null;
    },
    next() {
        this.go(this.active + 1);
    },
    previous() {
        this.go(this.active - 1);
    },
    dragStart(event) {
        if (event.pointerType === 'mouse') {
            return;
        }
        this.dragging = true;
        this.dragX = event.clientX;
    },
    dragEnd(event) {
        if (!this.dragging) {
            return;
        }
        this.dragging = false;
        const distance = event.clientX - this.dragX;
        if (Math.abs(distance) > 40) {
            distance < 0 ? this.next() : this.previous();
        }
    },
}));

/**
 * Property type picker state shared by the homepage search and the listing filters: an optional
 * main type (residential or commercial) narrows a checklist of sub-types. Plain members only, so it
 * can be spread into an Alpine component.
 */
const typePicker = ({ types = [], category = '', picked = [], labels = {} } = {}) => ({
    typeOptions: types,
    category: category ?? '',
    pickedTypes: picked.map(String),
    visibleTypes() {
        return this.category ? this.typeOptions.filter((option) => option.category === this.category) : this.typeOptions;
    },
    setCategory(next) {
        this.category = this.category === next ? '' : next;
        if (this.category) {
            this.pickedTypes = this.pickedTypes.filter((id) => this.typeOptions.find((option) => option.id === id)?.category === this.category);
        }
    },
    clearTypes() {
        this.category = '';
        this.pickedTypes = [];
    },
    typeText() {
        const names = this.pickedTypes.map((id) => this.typeOptions.find((option) => option.id === id)?.label).filter(Boolean);
        if (names.length > 1) {
            return (labels.typesMore ?? ':first +:count').replace(':first', names[0]).replace(':count', names.length - 1);
        }

        return names[0] ?? labels.categories?.[this.category] ?? '';
    },
});

/**
 * Listing filter bar property type dropdown.
 */
Alpine.data('uhTypePicker', (config = {}) => ({
    ...typePicker(config),
    open: false,
    get summary() {
        return this.typeText() || (config.labels?.any ?? '');
    },
    toggle() {
        this.open = !this.open;
    },
    close(returnFocus = false) {
        if (!this.open) {
            return;
        }
        this.open = false;
        if (returnFocus) {
            this.$refs.trigger?.focus();
        }
    },
}));

/**
 * Homepage search card. Location suggests areas, categories and listings as you type and falls
 * back to a keyword search; categories link straight to results and leave the type field alone.
 * Type and price open small panels. Choices clear by clicking them again.
 */
Alpine.data('uhHeroSearch', ({ initial = 'sale', initialMin = '', initialMax = '', suggestUrl = null, labels = {}, stops = {}, types = [] } = {}) => ({
    ...typePicker({ types, labels }),
    purpose: initial,
    open: null,
    query: '',
    area: '',
    home: '',
    locationType: '',
    min: String(initialMin ?? ''),
    max: String(initialMax ?? ''),
    minIndex: 0,
    maxIndex: (stops[initial] ?? [0]).length - 1,
    areas: [],
    properties: [],
    active: -1,
    loading: false,
    suggestTimer: null,
    suggestRequest: 0,
    submitting: false,
    init() {
        if (this.hasMin) {
            this.minIndex = Math.min(this.nearestStop(this.min, false), this.lastStop - 1);
        }
        if (this.hasMax) {
            this.maxIndex = Math.max(this.nearestStop(this.max, true), 1);
        }
        window.addEventListener('pageshow', () => {
            this.submitting = false;
            this.$root.querySelectorAll('input[name]').forEach((field) => {
                field.disabled = false;
            });
        });
    },
    get stopList() {
        return stops[this.purpose] ?? [0];
    },
    get lastStop() {
        return this.stopList.length - 1;
    },
    get rangeFrom() {
        return this.lastStop ? (this.minIndex / this.lastStop) * 100 : 0;
    },
    get rangeTo() {
        return this.lastStop ? (this.maxIndex / this.lastStop) * 100 : 100;
    },
    get hasMin() {
        return this.min !== '' && Number(this.min) > 0;
    },
    get hasMax() {
        return this.max !== '' && Number(this.max) > 0;
    },
    get typeSummary() {
        return this.typeText() || labels.anyType;
    },
    get priceSummary() {
        if (this.hasMin && this.hasMax) {
            return `${this.money(this.min)} – ${this.money(this.max, false)}`;
        }
        if (this.hasMin) {
            return labels.from.replace(':price', this.money(this.min));
        }

        return this.hasMax ? labels.upTo.replace(':price', this.money(this.max)) : labels.anyPrice;
    },
    get typeSuggestions() {
        const term = this.query.trim().toLowerCase();
        const matches = term
            ? this.typeOptions.filter((option) => option.label.toLowerCase().includes(term))
            : this.typeOptions.filter((option) => this.typeCount(option) > 0);

        return [...matches].sort((a, b) => this.typeCount(b) - this.typeCount(a)).slice(0, 4);
    },
    get propertyOffset() {
        return this.areas.length + this.typeSuggestions.length;
    },
    typeCount(option) {
        return option.counts?.[this.purpose] ?? 0;
    },
    typeMeta(option) {
        const count = this.typeCount(option);
        if (!count) {
            return '';
        }

        return count === 1 ? labels.oneHome : labels.homes.replace(':count', count.toLocaleString());
    },
    money(value, prefix = true) {
        const amount = Number(value) || 0;
        const trim = (number) => String(Number(number.toFixed(2)));
        let text = amount.toLocaleString('en-IN');
        if (amount >= 10_000_000) {
            text = `${trim(amount / 10_000_000)} ${labels.crore}`;
        } else if (amount >= 100_000) {
            text = `${trim(amount / 100_000)} ${labels.lakh}`;
        } else if (amount >= 1000) {
            text = `${trim(amount / 1000)}K`;
        }

        return prefix ? `BDT ${text}` : text;
    },
    show(panel) {
        this.open = panel;
        if (panel === 'location' && !this.areas.length && !this.properties.length) {
            this.suggest(0);
        }
    },
    toggle(panel) {
        this.open === panel ? this.close() : this.show(panel);
    },
    toggleLocation() {
        if (this.open === 'location') {
            this.close();
            return;
        }
        this.show('location');
        this.$root.querySelector('#find-location')?.focus();
    },
    close(returnFocus = false) {
        const panel = this.open;
        this.open = null;
        this.active = -1;
        if (returnFocus && panel && panel !== 'location') {
            this.$root.querySelector(`[aria-controls="find-${panel}-panel"]`)?.focus();
        }
    },
    typed() {
        this.area = '';
        this.home = '';
        this.locationType = '';
        this.active = -1;
        this.open = 'location';
        this.suggest();
    },
    suggest(delay = 200) {
        if (!suggestUrl) {
            return;
        }
        clearTimeout(this.suggestTimer);
        this.suggestTimer = setTimeout(async () => {
            const request = ++this.suggestRequest;
            this.loading = true;
            try {
                const params = new URLSearchParams({ q: this.query.trim(), include: 'properties' });
                const response = await fetch(`${suggestUrl}?${params}`, { headers: { Accept: 'application/json' } });
                const payload = response.ok ? await response.json() : {};
                if (request === this.suggestRequest) {
                    this.areas = (payload.results ?? []).slice(0, 5);
                    this.properties = payload.properties ?? [];
                }
            } catch {
                if (request === this.suggestRequest) {
                    this.areas = [];
                    this.properties = [];
                }
            } finally {
                if (request === this.suggestRequest) {
                    this.loading = false;
                }
            }
        }, delay);
    },
    move(step) {
        const total = this.propertyOffset + this.properties.length;
        if (this.open !== 'location') {
            this.show('location');
        }
        if (!total) {
            return;
        }
        this.active = (this.active + step + total) % total;
    },
    pickActive(event) {
        if (this.open !== 'location' || this.active < 0) {
            return;
        }
        event.preventDefault();
        if (this.active < this.typeSuggestions.length) {
            this.pickCategory(this.typeSuggestions[this.active]);
        } else if (this.active < this.propertyOffset) {
            this.pickArea(this.areas[this.active - this.typeSuggestions.length]);
        } else {
            const home = this.properties[this.active - this.propertyOffset];
            if (home) {
                this.pickHome(home);
            }
        }
    },
    clearLocation() {
        this.area = '';
        this.home = '';
        this.locationType = '';
        this.query = '';
        this.active = -1;
        this.suggest(0);
        this.$root.querySelector('#find-location')?.focus();
    },
    pickArea(item) {
        if (this.area === String(item.id)) {
            this.clearLocation();

            return;
        }
        this.area = String(item.id);
        this.home = '';
        this.locationType = '';
        this.query = item.text;
        this.close();
        this.suggest(0);
    },
    pickHome(item) {
        if (this.home === item.url) {
            this.clearLocation();

            return;
        }
        this.home = item.url;
        this.area = '';
        this.locationType = '';
        this.query = item.title;
        this.close();
        this.suggest(0);
    },
    pickCategory(option) {
        if (this.locationType === option.id) {
            this.clearLocation();

            return;
        }
        this.locationType = option.id;
        this.area = '';
        this.home = '';
        this.query = option.label;
        this.close();
        this.suggest(0);
    },
    nearestStop(value, roundUp) {
        const amount = Number(value) || 0;
        const list = this.stopList;
        if (roundUp) {
            const index = list.findIndex((stop) => stop >= amount);

            return index === -1 ? this.lastStop : index;
        }
        let index = 0;
        list.forEach((stop, position) => {
            if (stop <= amount) {
                index = position;
            }
        });

        return index;
    },
    slideMin() {
        this.minIndex = Math.min(this.minIndex, this.maxIndex - 1);
        this.min = this.minIndex === 0 ? '' : String(this.stopList[this.minIndex]);
    },
    slideMax() {
        this.maxIndex = Math.max(this.maxIndex, this.minIndex + 1);
        this.max = this.maxIndex === this.lastStop ? '' : String(this.stopList[this.maxIndex]);
    },
    typedMin() {
        this.minIndex = this.hasMin ? Math.min(this.nearestStop(this.min, false), this.lastStop - 1) : 0;
    },
    typedMax() {
        this.maxIndex = this.hasMax ? Math.max(this.nearestStop(this.max, true), 1) : this.lastStop;
    },
    resetPrice() {
        this.min = '';
        this.max = '';
        this.minIndex = 0;
        this.maxIndex = this.lastStop;
    },
    setPurpose(next) {
        this.purpose = next;
        this.resetPrice();
    },
    submit(event) {
        this.close();
        event.target.querySelectorAll('input[name]').forEach((field) => {
            if (field.value === '') {
                field.disabled = true;
            }
        });
        this.$nextTick(() => {
            this.submitting = true;
        });
    },
}));

/**
 * Home-loan EMI calculator using reducing-balance monthly instalments.
 */
Alpine.data('uhEmi', (price = 0) => ({
    price: Number(price) || 0,
    downPct: 20,
    rate: 9,
    years: 15,
    format(value) {
        return new Intl.NumberFormat('en-BD', { maximumFractionDigits: 0 }).format(Math.round(value || 0));
    },
    get downPayment() {
        return this.price * (this.downPct / 100);
    },
    get principal() {
        return Math.max(0, this.price - this.downPayment);
    },
    get months() {
        return Math.max(1, this.years * 12);
    },
    get monthlyRate() {
        return this.rate / 12 / 100;
    },
    get emi() {
        if (this.principal <= 0) {
            return 0;
        }
        const rate = this.monthlyRate;
        if (rate === 0) {
            return this.principal / this.months;
        }
        const factor = (1 + rate) ** this.months;

        return (this.principal * rate * factor) / (factor - 1);
    },
    get totalPayable() {
        return this.emi * this.months;
    },
    get totalInterest() {
        return Math.max(0, this.totalPayable - this.principal);
    },
}));

/**
 * Social sharing destinations and copy-link feedback for public content.
 */
Alpine.data('uhShare', (url, title, facebookAppId = null) => ({
    open: false,
    copied: false,
    copyTimer: null,
    get facebookHref() {
        return `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
    },
    get messengerHref() {
        if (!facebookAppId) {
            return '';
        }

        const params = new URLSearchParams({ app_id: facebookAppId, link: url, redirect_uri: url });

        return `https://www.facebook.com/dialog/send?${params.toString()}`;
    },
    get whatsappHref() {
        return `https://api.whatsapp.com/send?text=${encodeURIComponent(`${title} ${url}`.trim())}`;
    },
    get xHref() {
        const params = new URLSearchParams({ url, text: title });

        return `https://twitter.com/intent/tweet?${params.toString()}`;
    },
    get emailHref() {
        const params = new URLSearchParams({ subject: title, body: url });

        return `mailto:?${params.toString()}`;
    },
    async copyLink() {
        try {
            let copied = false;

            if (navigator.clipboard?.writeText && window.isSecureContext) {
                try {
                    await navigator.clipboard.writeText(url);
                    copied = true;
                } catch {
                    copied = false;
                }
            }

            if (!copied) {
                const field = document.createElement('textarea');
                field.value = url;
                field.setAttribute('readonly', '');
                field.style.position = 'fixed';
                field.style.opacity = '0';
                document.body.append(field);
                field.select();
                try {
                    copied = document.execCommand('copy');
                } finally {
                    field.remove();
                }

                if (!copied) {
                    throw new Error('Clipboard copy failed.');
                }
            }

            this.copied = true;
            window.clearTimeout(this.copyTimer);
            this.copyTimer = window.setTimeout(() => {
                this.copied = false;
            }, 1800);
        } catch {
            this.copied = false;
        }
    },
}));

/**
 * Fill lat/lng from the browser and resubmit the search form.
 */
Alpine.data('uhNearMe', () => ({
    locating: false,
    locate() {
        if (!navigator.geolocation) {
            return;
        }

        this.locating = true;
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const form = this.$el.closest('form');
                const setHidden = (name, value) => {
                    const field = form?.querySelector(`input[name="${name}"]`);
                    if (field) {
                        field.value = value;
                    }
                };
                setHidden('lat', position.coords.latitude.toFixed(6));
                setHidden('lng', position.coords.longitude.toFixed(6));
                if (form && !form.querySelector('input[name="radius_km"]')?.value) {
                    setHidden('radius_km', '3');
                }
                this.locating = false;
                form?.requestSubmit();
            },
            () => {
                this.locating = false;
            },
            { enableHighAccuracy: false, timeout: 8000, maximumAge: 60_000 },
        );
    },
}));

/**
 * Submit-once feedback for forms that post to the server.
 */
Alpine.data('uhForm', () => ({
    submitting: false,
    submit() {
        this.submitting = true;
    },
}));

Alpine.data('uhThemeEditor', (initialTheme = {}) => ({
    theme: JSON.parse(JSON.stringify(initialTheme)),
    expanded: { surfaces_text: false, hero_slider: false, mobile_nav: false },
    saving: false,
    error: '',
    success: '',
    errors: {},
    setBrand(key, value) {
        this.theme.brand[key] = value.toLowerCase();
        this.clearMessages();
    },
    setAdvanced(group, key, value) {
        this.theme.advanced[group][key] = value.toLowerCase();
        this.clearMessages();
    },
    pickerColor(value, fallback) {
        return /^#[0-9a-f]{6}$/i.test(value || '') ? value : fallback;
    },
    pickerFallback(group, key) {
        const brand = this.theme.brand;
        const fallbacks = {
            surfaces_text: { page_background: 'dominant', card_surface: 'dominant', primary_text: 'secondary', muted_text: 'secondary', border: 'dominant' },
            hero_slider: { overlay_color: 'secondary', slider_title: 'secondary', slider_subtitle: 'secondary', carousel_arrow: 'accent', carousel_dot: 'accent' },
            mobile_nav: { drawer_background: 'dominant', active_tab_highlight: 'accent', badge_fill: 'accent' },
        };
        const keyColors = {
        };

        return keyColors[`${group}.${key}`] || brand[fallbacks[group]?.[key] || 'dominant'];
    },
    fieldError(key) {
        return this.errors[key]?.[0] || '';
    },
    clearMessages() {
        this.error = '';
        this.success = '';
        this.errors = {};
    },
    resetGroup(group) {
        Object.keys(this.theme.advanced[group]).forEach((key) => {
            this.theme.advanced[group][key] = '';
        });
        this.clearMessages();
    },
    previewVariables() {
        const brand = this.theme.brand;
        const groups = this.theme.advanced;
        const surfaces = groups.surfaces_text;
        const hero = groups.hero_slider;
        const mobile = groups.mobile_nav;

        return {
            '--color-dominant': brand.dominant,
            '--color-secondary': brand.secondary,
            '--color-accent': brand.accent,
            '--color-page-background': surfaces.page_background || brand.dominant,
            '--color-card-surface': surfaces.card_surface || brand.dominant,
            '--color-body-text': surfaces.primary_text || brand.secondary,
            '--color-muted-text': surfaces.muted_text || 'color-mix(in srgb, var(--color-body-text) 58%, white)',
            '--color-divider': surfaces.border || 'color-mix(in srgb, var(--color-secondary) 12%, var(--color-dominant))',
            '--color-hero-overlay': hero.overlay_color || brand.secondary,
            '--color-hero-overlay-opacity': hero.overlay_opacity === '' ? '0.3' : hero.overlay_opacity,
            '--color-slider-title': hero.slider_title || 'var(--color-body-text)',
            '--color-slider-subtitle': hero.slider_subtitle || 'var(--color-muted-text)',
            '--color-carousel-arrow': hero.carousel_arrow || brand.accent,
            '--color-carousel-dot': hero.carousel_dot || brand.accent,
            '--color-mobile-nav-background': mobile.drawer_background || brand.dominant,
            '--color-mobile-active-highlight': mobile.active_tab_highlight || brand.accent,
            '--color-notification-badge': mobile.badge_fill || brand.accent,
        };
    },
    async save() {
        this.saving = true;
        this.clearMessages();

        try {
            const response = await fetch(this.$root.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.$root.querySelector('[name="_token"]')?.value || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ theme: this.theme }),
            });
            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                this.errors = payload.errors || {};
                this.error = payload.message || 'Could not save colors. Check the fields and try again.';
                return;
            }

            window.location.reload();
        } catch {
            this.error = 'Could not save colors. Check your connection and try again.';
        } finally {
            this.saving = false;
        }
    },
}));

/**
 * Property editor: hides residential fields for plot-type listings and the price for on-request listings.
 */
Alpine.data('uhPropertyForm', ({ profiles = {}, typeId = '', priceMode = 'fixed' } = {}) => ({
    submitting: false,
    profiles,
    typeId,
    priceMode,
    get isPlot() {
        return this.profiles[this.typeId] === 'plot';
    },
    submit() {
        this.submitting = true;
    },
}));

/**
 * Enquiry and visit forms. Submits over fetch so the visitor stays on the page; without
 * JavaScript the same form posts normally and lands on the thank-you page. The success
 * event fires only after the server confirms the lead was stored.
 */
const newToken = () =>
    window.crypto?.randomUUID
        ? window.crypto.randomUUID()
        : 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (char) => {
              const random = (Math.random() * 16) | 0;
              return (char === 'x' ? random : (random & 0x3) | 0x8).toString(16);
          });

Alpine.data('uhLeadForm', (formName = 'inquiry') => ({
    formName,
    submitting: false,
    started: false,
    done: false,
    message: '',
    error: '',
    errors: {},
    token: '',
    intent: '',
    slot: '',
    init() {
        this.token = this.$el.querySelector('input[name="submission_token"]')?.value || newToken();
        this.syncToken();
    },
    /**
     * Intent chips write a starting sentence into the message, but never replace what the visitor typed.
     */
    useIntent(text) {
        const field = this.$root.querySelector('textarea[name="message"]');
        if (!field) {
            return;
        }

        const current = field.value.trim();
        if (current === '' || current === this.intent) {
            field.value = this.intent === text ? '' : text;
            this.intent = this.intent === text ? '' : text;
        } else if (!current.includes(text)) {
            field.value = `${current}\n${text}`;
            this.intent = text;
        }

        this.start();
    },
    useSlot(value) {
        const field = this.$root.querySelector('input[name="preferred_at"]');
        if (!field) {
            return;
        }

        field.value = value;
        this.slot = value;
        this.start();
    },
    syncToken() {
        const field = this.$el.querySelector('input[name="submission_token"]');
        if (field) {
            field.value = this.token;
        }
    },
    start() {
        if (!this.started) {
            this.started = true;
            track('form_start', { form_name: this.formName });
        }
    },
    fieldError(name) {
        return (this.errors[name] || [])[0] || '';
    },
    async submit(event) {
        if (this.submitting) {
            event.preventDefault();
            return;
        }

        if (!window.fetch) {
            this.submitting = true;
            return;
        }

        event.preventDefault();
        this.submitting = true;
        this.error = '';
        this.errors = {};

        const form = event.target;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form),
                credentials: 'same-origin',
            });
            const payload = await response.json().catch(() => ({}));

            if (response.ok) {
                this.done = true;
                this.message = payload.message || '';
                if (payload.event) {
                    track(payload.event.event, payload.event);
                }
                return;
            }

            if (response.status === 422) {
                this.errors = payload.errors || {};
                this.error = payload.message || window.uhCopyText?.('form_check_fields') || '';
                track('form_error', { form_name: this.formName, fields: Object.keys(this.errors).join(',') });
            } else if (response.status === 419) {
                this.error = window.uhCopyText?.('form_expired') || '';
            } else if (response.status === 429) {
                this.error = window.uhCopyText?.('form_rate_limit') || '';
            } else {
                this.errors = payload.errors || {};
                this.error = payload.message || window.uhCopyText?.('form_send_error') || '';
            }
        } catch {
            this.error = window.uhCopyText?.('form_offline') || '';
        } finally {
            this.submitting = false;
        }
    },
}));

Alpine.data('uhCustomerAccount', (registerUrl, loginUrl) => ({
    modalOpen: false,
    accountReady: false,
    mode: 'register',
    busy: false,
    modalError: '',
    init() {
        this.updateModeFields();
    },
    toggle(event) {
        if (!event.target.checked) {
            return;
        }

        this.mode = 'register';
        this.modalError = '';
        this.modalOpen = true;
        this.$nextTick(() => {
            this.copyContactDetailsToModal();
            this.$root.querySelector('#account-name')?.focus();
        });
    },
    close() {
        this.modalOpen = false;
        if (!this.accountReady) {
            const checkbox = this.$root.querySelector('[name="create_account"]');
            if (checkbox) {
                checkbox.checked = false;
            }
        }
    },
    switchMode() {
        this.mode = this.mode === 'register' ? 'login' : 'register';
        this.modalError = '';
        this.$nextTick(() => {
            this.updateModeFields();
            if (this.mode === 'login') {
                const contactEmail = this.$root.querySelector('#property-contact-email')?.value;
                const email = this.$root.querySelector('#account-email');
                if (email && contactEmail) {
                    email.value = contactEmail;
                }
            } else {
                this.copyContactDetailsToModal();
            }
        });
    },
    updateModeFields() {
        const registerOnlyFields = ['name', 'phone', 'password_confirmation'];
        registerOnlyFields.forEach((name) => {
            const field = this.$root.querySelector(`#account-${name.replace('_', '-')}`);
            if (field) {
                field.disabled = this.mode !== 'register';
            }
        });
        const password = this.$root.querySelector('#account-password');
        if (password) {
            password.autocomplete = this.mode === 'register' ? 'new-password' : 'current-password';
        }
    },
    copyContactDetailsToModal() {
        const pairs = [
            ['#property-contact-name', '#account-name'],
            ['#property-contact-email', '#account-email'],
            ['#property-contact-phone', '#account-phone'],
        ];
        pairs.forEach(([source, target]) => {
            const value = this.$root.querySelector(source)?.value;
            const field = this.$root.querySelector(target);
            if (field && value) {
                field.value = value;
            }
        });
    },
    async submit(event) {
        if (this.busy) {
            return;
        }

        this.busy = true;
        this.modalError = '';
        const form = event.target;
        const endpoint = this.mode === 'register' ? registerUrl : loginUrl;

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form),
                credentials: 'same-origin',
            });
            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                const firstError = Object.values(payload.errors || {}).flat()[0];
                this.modalError = firstError || payload.message || window.uhCopyText?.('account_save_error') || '';
                return;
            }

            this.accountReady = true;
            const accountCheckbox = this.$root.querySelector('[name="create_account"]');
            if (accountCheckbox) {
                accountCheckbox.checked = true;
            }
            Object.entries(payload.user || {}).forEach(([name, value]) => {
                const field = this.$root.querySelector(`#property-contact-${name}`);
                if (field && value) {
                    field.value = value;
                    field.dispatchEvent(new Event('input', { bubbles: true }));
                }
            });
            this.modalOpen = false;
        } catch {
            this.modalError = window.uhCopyText?.('account_offline') || '';
        } finally {
            this.busy = false;
        }
    },
}));

/**
 * Analytics consent banner. Nothing third-party loads until the visitor chooses "Allow".
 */
Alpine.data('uhConsent', (current = null) => ({
    open: current !== 'granted' && current !== 'denied',
    choose(choice) {
        const cookie = window.uhAnalytics?.cookie || 'uh_consent';
        document.cookie = `${cookie}=${choice}; Max-Age=${60 * 60 * 24 * 180}; Path=/; SameSite=Lax${location.protocol === 'https:' ? '; Secure' : ''}`;
        this.open = false;
        bootAnalytics(choice);
    },
}));

/**
 * Collapsible staff-desk sidebar. Group state and the desktop collapse preference
 * stay in localStorage so the layout does not reset on every page.
 */
Alpine.data('uhAdminShell', (activeGroups = []) => ({
    mobile: false,
    collapsed: window.localStorage.getItem('uh-admin-collapsed') === '1',
    groups: {},
    drawer: null,
    target: null,
    submitting: false,
    error: '',
    errors: {},
    init() {
        const stored = window.localStorage.getItem('uh-admin-groups');
        let saved = {};
        if (stored) {
            try {
                saved = JSON.parse(stored) || {};
            } catch {
                saved = {};
            }
        }
        this.groups = saved;
        activeGroups.forEach((name) => {
            this.groups[name] = true;
        });
    },
    groupOpen(name) {
        return this.groups[name] === true;
    },
    toggleGroup(name) {
        this.groups[name] = !this.groupOpen(name);
        window.localStorage.setItem('uh-admin-groups', JSON.stringify(this.groups));
    },
    toggleCollapsed() {
        this.collapsed = ! this.collapsed;
        window.localStorage.setItem('uh-admin-collapsed', this.collapsed ? '1' : '0');
    },
    toggleSidebar() {
        if (window.innerWidth < 768) {
            this.mobile = ! this.mobile;
        } else {
            this.toggleCollapsed();
        }
    },
    openMobile() {
        this.mobile = true;
    },
    closeMobile() {
        this.mobile = false;
    },
    isOpen(name) {
        return this.drawer === name;
    },
    open(detail = {}) {
        this.drawer = typeof detail === 'string' ? detail : (detail.kind ?? null);
        this.target = typeof detail === 'string' ? null : (detail.target ?? null);
        this.submitting = false;
        this.error = '';
        this.errors = {};
        syncAdminSheets(this.drawer);
        this.$nextTick(() => {
            requestAnimationFrame(() => bootAdminSelects());
        });
    },
    close() {
        this.drawer = null;
        this.target = null;
        this.submitting = false;
        this.error = '';
        this.errors = {};
        syncAdminSheets(null);
    },
    async submit(event) {
        event.preventDefault();
        const form = event.target;
        this.submitting = true;
        this.error = '';
        this.errors = {};

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(form),
            });
            const payload = await response.json().catch(() => ({}));

            if (! response.ok) {
                this.errors = payload.errors ?? {};
                this.error = payload.message || 'Could not save. Check the fields and try again.';
                this.submitting = false;

                return;
            }

            applySelectOption(this.target, payload);
            form.reset();
            this.close();
        } catch {
            this.error = 'Could not save. Check the connection and try again.';
            this.submitting = false;
        }
    },
}));

Alpine.data('uhAdminDrawers', (initial = null) => ({
    drawer: initial,
    open(name) {
        this.drawer = name;
        document.body.style.overflow = 'hidden';
        syncAdminSheets(name);
        this.$nextTick(() => {
            this.$root.querySelector(`[data-drawer="${name}"] input:not([type="hidden"]), [data-drawer="${name}"] textarea, [data-drawer="${name}"] select`)?.focus();
        });
    },
    close() {
        this.drawer = null;
        document.body.style.overflow = '';
        syncAdminSheets(null);
    },
    isOpen(name) {
        return this.drawer === name;
    },
    onEscape(event) {
        if (event.key === 'Escape' && this.drawer) {
            this.close();
        }
    },
    init() {
        this._onEscape = (event) => this.onEscape(event);
        window.addEventListener('keydown', this._onEscape);
        if (this.drawer) {
            document.body.style.overflow = 'hidden';
        }
    },
    destroy() {
        window.removeEventListener('keydown', this._onEscape);
        document.body.style.overflow = '';
    },
}));

/**
 * Staff media manager: multi-file upload review, reordering, and batch saving.
 */
Alpine.data('uhMediaUpload', (config = {}) => ({
    ownerType: config.ownerType || '',
    ownerId: config.ownerId || 0,
    collection: config.collection || 'gallery',
    reorderUrl: config.reorderUrl || '',
    batchUrl: config.batchUrl || '',
    coverId: config.coverId || null,
    itemIds: Array.isArray(config.itemIds) ? [...config.itemIds] : [],
    itemsData: {},
    pendingFiles: [],
    submitting: false,
    batchSubmitting: false,
    dragging: false,
    filename: '',
    filesize: '',
    preview: '',
    previews: [],
    statusNotice: '',
    statusTone: 'info',
    hasUnsavedChanges: false,
    statusTimeout: null,

    init() {
        if (config.items && Array.isArray(config.items)) {
            config.items.forEach((item) => {
                this.itemsData[item.id] = {
                    alt_text: item.alt_text ?? '',
                    is_public: Boolean(item.is_public),
                    make_cover: this.coverId === item.id,
                };
            });
        }
        window.addEventListener('beforeunload', (e) => {
            if (this.hasUnsavedChanges || this.pendingFiles.length > 0) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    },

    flashNotice(message, tone = 'info') {
        this.statusNotice = message;
        this.statusTone = tone;
        clearTimeout(this.statusTimeout);
        if (tone === 'success') {
            this.statusTimeout = setTimeout(() => {
                this.statusNotice = '';
            }, 5000);
        }
    },

    pick() {
        this.$refs.file?.click();
    },

    dropped(event, autoSubmit = false) {
        this.dragging = false;
        const input = this.$refs.file;
        const accepted = (input?.accept ?? '').split(',').map((type) => type.trim()).filter(Boolean);
        const files = [...(event.dataTransfer?.files ?? [])]
            .filter((file) => accepted.length === 0 || accepted.includes(file.type))
            .slice(0, input?.multiple ? 20 : 1);
        if (! input || files.length === 0) {
            return;
        }
        const transfer = new DataTransfer();
        files.forEach((file) => transfer.items.add(file));
        input.files = transfer.files;
        this.chosen(autoSubmit);
    },

    chosen(autoSubmit = false) {
        const files = [...(this.$refs.file?.files ?? [])];
        this.pendingFiles.forEach((pf) => pf.previewUrl && URL.revokeObjectURL(pf.previewUrl));
        this.previews.forEach((url) => URL.revokeObjectURL(url));

        this.pendingFiles = files.map((file, idx) => ({
            id: 'pf_' + idx + '_' + Math.random().toString(36).substring(2, 7),
            name: file.name,
            size: file.size,
            sizeFormatted: this.formatSize(file.size),
            previewUrl: file.type?.startsWith('image/') ? URL.createObjectURL(file) : null,
        }));

        const totalBytes = files.reduce((total, item) => total + item.size, 0);
        this.filename = files.length > 1 ? `${files.length} files` : (files[0]?.name ?? '');
        this.filesize = files.length > 0 ? this.formatSize(totalBytes) : '';
        this.previews = this.pendingFiles.filter((pf) => pf.previewUrl).slice(0, 12).map((pf) => pf.previewUrl);
        this.preview = this.previews[0] ?? '';

        if (autoSubmit && files.length > 0 && this.$refs.file?.form) {
            this.uploadPending();
        }
    },

    removePending(index) {
        if (! this.$refs.file || index < 0 || index >= this.pendingFiles.length) {
            return;
        }
        const pf = this.pendingFiles[index];
        if (pf && pf.previewUrl) {
            URL.revokeObjectURL(pf.previewUrl);
        }
        this.pendingFiles.splice(index, 1);

        const currentFiles = [...(this.$refs.file.files ?? [])];
        currentFiles.splice(index, 1);
        const transfer = new DataTransfer();
        currentFiles.forEach((f) => transfer.items.add(f));
        this.$refs.file.files = transfer.files;

        if (this.pendingFiles.length === 0) {
            this.clear();
        } else {
            const totalBytes = currentFiles.reduce((total, item) => total + item.size, 0);
            this.filename = currentFiles.length > 1 ? `${currentFiles.length} files` : (currentFiles[0]?.name ?? '');
            this.filesize = this.formatSize(totalBytes);
            this.previews = this.pendingFiles.filter((p) => p.previewUrl).slice(0, 12).map((p) => p.previewUrl);
            this.preview = this.previews[0] ?? '';
        }
    },

    uploadPending() {
        if (! this.$refs.file?.form || this.pendingFiles.length === 0) {
            return;
        }
        this.submitting = true;
        this.$refs.file.form.requestSubmit();
    },

    formatSize(bytes) {
        if (bytes >= 1048576) {
            return `${(bytes / 1048576).toFixed(1)} MB`;
        }

        return `${Math.max(1, Math.round(bytes / 1024))} KB`;
    },

    clear() {
        this.pendingFiles.forEach((pf) => pf.previewUrl && URL.revokeObjectURL(pf.previewUrl));
        this.previews.forEach((url) => URL.revokeObjectURL(url));
        this.pendingFiles = [];
        this.filename = '';
        this.filesize = '';
        this.preview = '';
        this.previews = [];
        this.submitting = false;
        if (this.$refs.file) {
            this.$refs.file.value = '';
        }
    },

    submit() {
        this.submitting = true;
    },

    isFirst(id) {
        return this.itemIds.length > 0 && this.itemIds[0] === id;
    },

    isLast(id) {
        return this.itemIds.length > 0 && this.itemIds[this.itemIds.length - 1] === id;
    },

    moveItem(id, direction) {
        const index = this.itemIds.indexOf(id);
        if (index === -1) {
            return;
        }
        const targetIndex = index + direction;
        if (targetIndex < 0 || targetIndex >= this.itemIds.length) {
            return;
        }

        const temp = this.itemIds[index];
        this.itemIds[index] = this.itemIds[targetIndex];
        this.itemIds[targetIndex] = temp;

        const currentEl = document.getElementById('media-item-' + id);
        if (currentEl && currentEl.parentNode) {
            if (direction === -1 && currentEl.previousElementSibling) {
                currentEl.parentNode.insertBefore(currentEl, currentEl.previousElementSibling);
            } else if (direction === 1 && currentEl.nextElementSibling) {
                currentEl.parentNode.insertBefore(currentEl.nextElementSibling, currentEl);
            }
        }

        if (this.reorderUrl) {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            fetch(this.reorderUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    owner_type: this.ownerType,
                    owner_id: this.ownerId,
                    collection: this.collection,
                    ordered_ids: this.itemIds,
                }),
            }).then((res) => {
                if (res.ok) {
                    this.flashNotice('Image order updated.', 'success');
                } else {
                    this.flashNotice('Failed to update image order.', 'danger');
                }
            }).catch(() => {
                this.flashNotice('Network error while reordering.', 'danger');
            });
        }
    },

    setCover(id) {
        this.coverId = id;
        Object.keys(this.itemsData).forEach((k) => {
            if (this.itemsData[k]) {
                this.itemsData[k].make_cover = (parseInt(k, 10) === id);
            }
        });
        this.hasUnsavedChanges = true;
        this.flashNotice('Cover image selected. Click "Save changes" to persist.', 'info');
    },

    updateItemAlt(id, value) {
        if (! this.itemsData[id]) {
            this.itemsData[id] = { alt_text: value, is_public: false, make_cover: this.coverId === id };
        } else {
            this.itemsData[id].alt_text = value;
        }
        this.hasUnsavedChanges = true;
    },

    updateItemPublic(id, value) {
        if (! this.itemsData[id]) {
            this.itemsData[id] = { alt_text: '', is_public: value, make_cover: this.coverId === id };
        } else {
            this.itemsData[id].is_public = value;
        }
        this.hasUnsavedChanges = true;
    },

    saveAllDetails() {
        if (! this.batchUrl) {
            return;
        }

        for (const id of this.itemIds) {
            const item = this.itemsData[id];
            if (item && item.is_public && (! item.alt_text || ! item.alt_text.trim())) {
                this.flashNotice('Please add descriptive alt text to all public images before saving.', 'danger');
                const inputEl = document.getElementById('alt-' + id);
                if (inputEl) {
                    inputEl.focus();
                    inputEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return;
            }
        }

        this.batchSubmitting = true;
        const itemsPayload = this.itemIds.map((id) => {
            const data = this.itemsData[id] || {};
            return {
                id: id,
                alt_text: data.alt_text ?? '',
                is_public: data.is_public ? 1 : 0,
                make_cover: (this.coverId === id) ? 1 : 0,
            };
        });

        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        fetch(this.batchUrl, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ items: itemsPayload }),
        }).then((res) => res.json()).then((data) => {
            this.batchSubmitting = false;
            if (data.ok) {
                this.hasUnsavedChanges = false;
                this.flashNotice(`${data.updated || itemsPayload.length} images updated successfully.`, 'success');
            } else {
                this.flashNotice(data.message || 'Failed to save image changes.', 'danger');
            }
        }).catch(() => {
            this.batchSubmitting = false;
            this.flashNotice('Network error while saving.', 'danger');
        });
    },
}));

/**
 * Confirmation gate for destructive staff actions.
 */

Alpine.data('uhConfirm', (message = 'Are you sure?') => ({
    message,
    confirm(event) {
        if (!window.confirm(this.message)) {
            event.preventDefault();
            event.stopPropagation();
        }
    },
}));

/**
 * Below-the-fold blocks marked data-reveal rise in once as they enter the viewport.
 * Content above the fold, hidden content and reduced-motion visitors are never hidden.
 */
const bootReveals = () => {
    const targets = [...document.querySelectorAll('body.uh-home [data-reveal]:not(.is-pending)')];

    if (! targets.length || prefersReducedMotion() || ! ('IntersectionObserver' in window)) {
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-in');
                observer.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -6% 0px', threshold: 0.06 });

    const fold = window.innerHeight * 0.94;

    targets.forEach((target) => {
        const rect = target.getBoundingClientRect();

        if (rect.height === 0 || rect.top < fold) {
            return;
        }

        target.classList.add('is-pending');
        observer.observe(target);
    });
};

/**
 * Lazy photographs settle in once decoded instead of popping in. Images whose visibility is
 * already managed by their component (opacity 0 until active) are left alone.
 */
const bootImageFades = () => {
    if (prefersReducedMotion()) {
        return;
    }

    document.querySelectorAll('body.uh-home img[loading="lazy"]').forEach((image) => {
        if (image.complete || getComputedStyle(image).opacity !== '1') {
            return;
        }

        image.classList.add('uh-img-pending');
        image.addEventListener('load', () => {
            image.classList.remove('uh-img-pending');
            image.animate?.([{ opacity: 0 }, { opacity: 1 }], { duration: 700, easing: 'cubic-bezier(0.28, 0.11, 0.32, 1)' });
        }, { once: true });
        image.addEventListener('error', () => image.classList.remove('uh-img-pending'), { once: true });
    });
};

Alpine.data('adminLiveSearch', (endpoint) => ({
    endpoint: endpoint,
    q: '',
    isOpen: false,
    loading: false,
    results: [],
    total: 0,
    selectedIndex: -1,
    debounceTimer: null,
    onInput() {
        clearTimeout(this.debounceTimer);
        const term = this.q.trim();
        if (term.length < 2) {
            this.results = [];
            this.total = 0;
            this.isOpen = false;
            this.selectedIndex = -1;
            return;
        }
        this.loading = true;
        this.isOpen = true;
        this.debounceTimer = setTimeout(() => {
            fetch(`${this.endpoint}?q=${encodeURIComponent(term)}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then((r) => r.json())
            .then((data) => {
                this.results = data.results || [];
                this.total = data.total || 0;
                this.loading = false;
                this.selectedIndex = this.results.length > 0 ? 0 : -1;
            })
            .catch(() => {
                this.loading = false;
            });
        }, 160);
    },
    navigate(step) {
        if (!this.isOpen || this.results.length === 0) {
            return;
        }
        this.selectedIndex = (this.selectedIndex + step + this.results.length) % this.results.length;
    },
    close() {
        this.isOpen = false;
        this.selectedIndex = -1;
    }
}));

Alpine.data('adminNotifications', (unreadUrl, markAllUrl, initialCount = 0) => ({
    unreadUrl: unreadUrl,
    markAllUrl: markAllUrl,
    open: false,
    unreadCount: initialCount,
    notifications: [],
    loading: false,
    pollTimer: null,
    init() {
        this.fetchLatest();
        this.pollTimer = setInterval(() => {
            if (document.visibilityState === 'visible') {
                this.fetchLatest();
            }
        }, 30000);
        window.addEventListener('focus', () => this.fetchLatest());
    },
    fetchLatest() {
        fetch(this.unreadUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then((r) => r.json())
        .then((data) => {
            this.unreadCount = data.unread_count ?? this.unreadCount;
            this.notifications = data.notifications || [];
        })
        .catch(() => {});
    },
    markAllRead() {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        fetch(this.markAllUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then((r) => r.json())
        .then(() => {
            this.unreadCount = 0;
            this.notifications = [];
        })
        .catch(() => {});
    },
    markRead(id, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        fetch(`/admin/notifications/${id}`, {
            method: 'PATCH',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then((r) => r.json())
        .then((data) => {
            this.notifications = this.notifications.filter((n) => n.id !== id);
            if (typeof data.unread_count !== 'undefined') {
                this.unreadCount = data.unread_count;
            } else {
                this.unreadCount = Math.max(0, this.unreadCount - 1);
            }
        })
        .catch(() => {});
    }
}));

registerSavedStore(Alpine);
registerAdminForms(Alpine);

Alpine.magic('trapFocus', () => trapFocus);

window.Alpine = Alpine;
window.uhTrack = track;
Alpine.start();
bootAdminSelects();

document.addEventListener('click', (event) => {
    const row = event.target.closest?.('.uh-admin-clickrow');
    if (! row || event.target.closest('a, button, input, label, select, textarea')) {
        return;
    }
    row.querySelector('.uh-admin-row-main')?.click();
});

/**
 * Admin forms marked data-unsaved-guard warn before the page is left with edits that were never saved.
 */
const bootUnsavedGuards = () => {
    if (! document.body.classList.contains('uh-admin')) {
        return;
    }

    const dirty = new Set();

    document.querySelectorAll('form[data-unsaved-guard]').forEach((form) => {
        const mark = () => dirty.add(form);
        form.addEventListener('input', mark);
        form.addEventListener('change', mark);
        form.addEventListener('submit', () => dirty.clear());
    });

    window.addEventListener('beforeunload', (event) => {
        if (dirty.size > 0) {
            event.preventDefault();
            event.returnValue = '';
        }
    });

    window.addEventListener('keydown', (event) => {
        if (! (event.ctrlKey || event.metaKey) || event.key.toLowerCase() !== 's') {
            return;
        }
        const focusedForm = document.activeElement?.closest?.('form');
        const form = focusedForm?.querySelector('input[name="_token"]')
            ? focusedForm
            : document.querySelector('form[data-unsaved-guard]');
        if (! form) {
            return;
        }
        event.preventDefault();
        form.requestSubmit();
    });
};

document.addEventListener('DOMContentLoaded', () => {
    bootAdminSelects();
    bootUnsavedGuards();
    bootPublicSelects();
    bootMaps();
    bindTrackedClicks();
    bootReveals();
    bootImageFades();

    const consent = window.uhAnalytics?.autoConsent
        ? 'granted'
        : document.cookie.match(new RegExp(`(?:^|; )${window.uhAnalytics?.cookie || 'uh_consent'}=([^;]*)`))?.[1];
    bootAnalytics(consent);

    const pageEvents = document.getElementById('uh-page-events');
    if (pageEvents) {
        try {
            JSON.parse(pageEvents.textContent || '[]').forEach((payload) => track(payload.event, payload));
        } catch {
            // Malformed payloads are ignored rather than breaking the page.
        }
    }
});

window.addEventListener('uh:refresh-maps', refreshMaps);
window.addEventListener('uh:refresh-map-data', refreshMapData);
