import Alpine from 'alpinejs';
import { track, bootAnalytics, bindTrackedClicks } from './analytics';
import { applySelectOption, bootAdminSelects } from './admin-selects';
import { bootPublicSelects } from './public-selects';
import { registerSavedStore } from './saved';
import { bootMaps, refreshMaps } from './maps';

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
Alpine.data('uhCoverSlides', (count = 0) => ({
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
        }, 6000);
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
    select(index) {
        this.active = Math.max(0, Math.min(index, this.count - 1));
    },
    next() {
        this.active = this.count ? (this.active + 1) % this.count : 0;
    },
    previous() {
        this.active = this.count ? (this.active - 1 + this.count) % this.count : 0;
    },
    open(index = null) {
        if (index !== null) {
            this.select(index);
        }
        this.lightbox = true;
        document.body.style.overflow = 'hidden';
        this.$nextTick(() => this.$refs.closeLightbox?.focus());
    },
    close() {
        this.lightbox = false;
        document.body.style.overflow = '';
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
    preview: null,
    slide: 0,
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
    setLayout(layout) {
        this.layout = layout;
        try {
            localStorage.setItem('uh-browse-layout', layout);
        } catch {
            // Private browsing can block storage; the choice still applies this visit.
        }
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
 * listing previews its second photo when available; listing selection never advances on a timer.
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
Alpine.data('uhHeroSearch', ({ initial = 'sale', suggestUrl = null, labels = {}, stops = {}, types = [] } = {}) => ({
    ...typePicker({ types, labels }),
    purpose: initial,
    open: null,
    query: '',
    area: '',
    home: '',
    locationType: '',
    min: '',
    max: '',
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
 * Native share, with a copy fallback for browsers that do not support it.
 */
Alpine.data('uhShare', (url, title) => ({
    copied: false,
    async share() {
        if (navigator.share) {
            try {
                await navigator.share({ title, url });
            } catch {
                // The visitor dismissed the sheet.
            }
            return;
        }

        try {
            await navigator.clipboard.writeText(url);
            this.copied = true;
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
                this.error = payload.message || 'Please check the highlighted fields.';
                track('form_error', { form_name: this.formName, fields: Object.keys(this.errors).join(',') });
            } else if (response.status === 419) {
                this.error = 'This page has expired. Refresh and try again.';
            } else {
                this.error = payload.message || 'We could not send your request. Please try again or call us.';
            }
        } catch {
            this.error = 'You appear to be offline. Check your connection and try again, or call us.';
        } finally {
            this.submitting = false;
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
 * Staff media picker: local preview of the chosen file, then the existing upload form.
 */
Alpine.data('uhMediaUpload', () => ({
    submitting: false,
    filename: '',
    filesize: '',
    preview: '',
    pick() {
        this.$refs.file?.click();
    },
    chosen(autoSubmit = false) {
        const files = [...(this.$refs.file?.files ?? [])];
        const file = files[0];
        if (this.preview) {
            URL.revokeObjectURL(this.preview);
        }
        this.filename = files.length > 1 ? `${files.length} files` : (file?.name ?? '');
        this.filesize = file ? this.formatSize(files.reduce((total, item) => total + item.size, 0)) : '';
        this.preview = file?.type?.startsWith('image/') ? URL.createObjectURL(file) : '';
        if (autoSubmit && file && this.$refs.file?.form) {
            this.submitting = true;
            this.$refs.file.form.requestSubmit();
        }
    },
    formatSize(bytes) {
        if (bytes >= 1048576) {
            return `${(bytes / 1048576).toFixed(1)} MB`;
        }

        return `${Math.max(1, Math.round(bytes / 1024))} KB`;
    },
    clear() {
        if (this.preview) {
            URL.revokeObjectURL(this.preview);
        }
        this.filename = '';
        this.filesize = '';
        this.preview = '';
        this.submitting = false;
        if (this.$refs.file) {
            this.$refs.file.value = '';
        }
    },
    submit() {
        this.submitting = true;
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

registerSavedStore(Alpine);

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

document.addEventListener('DOMContentLoaded', () => {
    bootAdminSelects();
    bootPublicSelects();
    bootMaps();
    bindTrackedClicks();
    bootReveals();
    bootImageFades();

    const consent = document.cookie.match(new RegExp(`(?:^|; )${window.uhAnalytics?.cookie || 'uh_consent'}=([^;]*)`))?.[1];
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
