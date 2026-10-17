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

const restoreAdminMobileTable = (table) => {
    const mobileCards = table.__uhAdminMobileCards;
    if (!mobileCards) {
        return;
    }

    mobileCards.rows.forEach(({ cells }) => {
        cells.forEach(({ cell, content }) => {
            while (content.firstChild) {
                cell.appendChild(content.firstChild);
            }
        });
    });

    mobileCards.source.removeAttribute('data-mobile-table-source');
    mobileCards.source.removeAttribute('aria-hidden');
    mobileCards.cards.remove();
    delete table.__uhAdminMobileCards;
};

const buildAdminMobileTableCards = (table) => {
    if (table.__uhAdminMobileCards) {
        return;
    }

    const source = table.closest('.uh-table-scroll');
    const headingRow = table.tHead?.rows?.[0];
    if (!source || source.closest('.hidden') || !headingRow) {
        return;
    }

    const labels = Array.from(headingRow.cells, (heading) =>
        heading.textContent.replace(/\s+/g, ' ').trim(),
    );
    const cards = document.createElement('div');
    cards.className = 'uh-admin-mobile-table-cards';
    const rows = [];

    Array.from(table.tBodies).forEach((body) => {
        Array.from(body.rows).forEach((row) => {
            const cells = Array.from(row.cells);
            const isActionCell = (cell, index) =>
                cell.classList.contains('uh-admin-row-actions')
                || Boolean(cell.querySelector('.uh-admin-row-actions'))
                || /^(actions?)$/i.test(labels[index] ?? '');
            const actionIndexes = new Set(cells.flatMap((cell, index) => isActionCell(cell, index) ? [index] : []));
            const priceIndex = cells.findIndex((cell, index) =>
                !actionIndexes.has(index) && /price|amount|cost/i.test(labels[index] ?? ''),
            );
            const hasText = (cell) => cell.textContent.replace(/\s+/g, ' ').trim().length > 0;
            let primaryIndex = cells.findIndex((cell, index) =>
                !actionIndexes.has(index) && index !== priceIndex && hasText(cell),
            );
            if (primaryIndex < 0) {
                primaryIndex = cells.findIndex((cell, index) => !actionIndexes.has(index) && index !== priceIndex);
            }

            const visualIndex = cells.findIndex((cell, index) =>
                index !== primaryIndex
                && !actionIndexes.has(index)
                && index !== priceIndex
                && !hasText(cell)
                && Boolean(cell.querySelector('img, svg')),
            );
            const card = document.createElement('article');
            card.className = 'uh-admin-property-card uh-admin-table-card';
            if (row.classList.contains('uh-admin-clickrow')) {
                card.classList.add('uh-admin-clickrow');
            }
            for (const attribute of Array.from(row.attributes)) {
                if (attribute.name.startsWith('data-') || attribute.name.startsWith('aria-') || attribute.name.startsWith('x-show') || attribute.name.startsWith('x-transition')) {
                    card.setAttribute(attribute.name, attribute.value);
                }
            }

            const cellTargets = new Map();
            const rememberTarget = (index, target) => {
                if (index < 0) {
                    return;
                }
                cellTargets.set(index, target);
            };
            const moveCellContent = (index, target) => {
                if (index < 0) {
                    return;
                }
                const cell = cells[index];
                while (cell.firstChild) {
                    target.appendChild(cell.firstChild);
                }
                rememberTarget(index, target);
                if (cell.dir) {
                    target.dir = cell.dir;
                }
            };

            const heading = document.createElement('div');
            heading.className = 'uh-admin-property-card-heading uh-admin-table-card-heading';
            if (visualIndex >= 0) {
                const visual = document.createElement('span');
                visual.className = 'uh-admin-table-card-visual';
                moveCellContent(visualIndex, visual);
                heading.appendChild(visual);
            }
            const primary = document.createElement('div');
            primary.className = 'uh-admin-table-card-primary';
            moveCellContent(primaryIndex, primary);
            heading.appendChild(primary);
            if (priceIndex >= 0) {
                const price = document.createElement('span');
                price.className = 'uh-admin-property-price uh-numeric';
                moveCellContent(priceIndex, price);
                heading.appendChild(price);
            }
            card.appendChild(heading);

            const meta = document.createElement('dl');
            meta.className = 'uh-admin-property-card-meta';
            cells.forEach((cell, index) => {
                if (index === primaryIndex || index === visualIndex || index === priceIndex || actionIndexes.has(index)) {
                    return;
                }

                const field = document.createElement('div');
                const label = document.createElement('dt');
                label.textContent = labels[index] || 'Details';
                const value = document.createElement('dd');
                moveCellContent(index, value);
                field.append(label, value);
                meta.appendChild(field);
            });
            if (meta.childElementCount > 0) {
                card.appendChild(meta);
            }

            const actionIndexesInRow = Array.from(actionIndexes);
            if (actionIndexesInRow.length > 0) {
                const actions = document.createElement('div');
                actions.className = 'uh-admin-table-card-actions';
                actionIndexesInRow.forEach((index) => moveCellContent(index, actions));
                card.appendChild(actions);
            }

            cards.appendChild(card);
            rows.push({ row, cells: Array.from(cellTargets, ([index, content]) => ({ cell: cells[index], content })) });
        });
    });

    source.dataset.mobileTableSource = 'true';
    source.setAttribute('aria-hidden', 'true');
    source.after(cards);
    table.__uhAdminMobileCards = { source, cards, rows };
    window.Alpine?.initTree(cards);
};

const syncAdminMobileTables = (isMobile = window.matchMedia('(max-width: 767px)').matches) => {
    document.querySelectorAll('body.uh-admin .uh-table-scroll .uh-table').forEach((table) => {
        if (isMobile) {
            buildAdminMobileTableCards(table);
        } else {
            restoreAdminMobileTable(table);
        }
    });
};

const adminMobileTableQuery = window.matchMedia('(max-width: 767px)');
if (document.body?.classList.contains('uh-admin')) {
    adminMobileTableQuery.addEventListener('change', (event) => syncAdminMobileTables(event.matches));
}

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
    mediaView: 'photos',
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
    filterCardOpen: true,
    lastFilterScrollY: 0,
    submitting: false,
    more: hasAdvanced,
    layout: 'grid',
    mapMode: 'split',
    preview: null,
    slide: 0,
    requestController: null,
    init() {
        this.lastFilterScrollY = window.scrollY;
        this.filterCardOpen = true;

        const syncFilterCardToScrollPosition = () => {
            this.lastFilterScrollY = window.scrollY;
            this.filterCardOpen = window.scrollY <= 2;
        };

        window.requestAnimationFrame(syncFilterCardToScrollPosition);
        window.addEventListener('pageshow', syncFilterCardToScrollPosition);

        try {
            const viewFromUrl = new URLSearchParams(window.location.search).get('view');
            if (viewFromUrl === 'grid' || viewFromUrl === 'list') {
                this.layout = viewFromUrl;
                localStorage.setItem('uh-browse-layout', viewFromUrl);
            } else {
                const stored = localStorage.getItem('uh-browse-layout');
                if (stored === 'grid' || stored === 'list') {
                    this.layout = stored;
                }
            }
            window.history.replaceState({ ...window.history.state, view: this.layout }, '');
        } catch {
            this.layout = 'grid';
        }

        window.addEventListener('popstate', (event) => {
            const viewFromUrl = new URLSearchParams(window.location.search).get('view');
            const viewFromHistory = event.state?.view;
            const restoredView = ['grid', 'list'].includes(viewFromUrl) ? viewFromUrl : viewFromHistory;
            if (restoredView === 'grid' || restoredView === 'list') {
                this.layout = restoredView;
                try {
                    localStorage.setItem('uh-browse-layout', restoredView);
                } catch {
                    // The URL and active state still restore when storage is unavailable.
                }
            }
        });
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
            window.history.pushState({ view: this.layout }, '', url);
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
        if (layout !== 'grid' && layout !== 'list') {
            return;
        }

        this.layout = layout;
        try {
            localStorage.setItem('uh-browse-layout', layout);
        } catch {
            // Private browsing can block storage; the choice still applies this visit.
        }

        const url = new URL(window.location.href);
        if (url.searchParams.get('view') !== layout) {
            url.searchParams.set('view', layout);
            window.history.pushState({}, '', url);
        }
    },
    handleFilterCardScroll() {
        const currentScrollY = window.scrollY;

        if (currentScrollY <= 2) {
            this.filterCardOpen = true;
        } else if (currentScrollY > this.lastFilterScrollY + 4) {
            this.filterCardOpen = false;
        }

        this.lastFilterScrollY = currentScrollY;
    },
    toggleMapMode() {
        this.mapMode = this.mapMode === 'full' ? 'split' : 'full';
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
 * Admin form section tabs track the admin content pane, which scrolls separately from the window.
 */
Alpine.data('uhAdminFormSteps', () => ({
    current: null,
    sections: [],
    scroller: null,
    ticking: false,
    onScroll: null,
    onClick: null,
    init() {
        this.scroller = this.$el.closest('.dd-content');
        this.sections = [...this.$el.querySelectorAll('a[href^="#step-"]')]
            .map((link) => document.getElementById(link.getAttribute('href').slice(1)))
            .filter((section, index, sections) => section && sections.indexOf(section) === index);
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
            const link = event.target.closest('a[href^="#step-"]');
            if (!link) {
                return;
            }
            this.current = link.getAttribute('href').slice(1);
            this.reveal(link);
        };
        this.scroller?.addEventListener('scroll', this.onScroll, { passive: true });
        this.$el.addEventListener('click', this.onClick);
        this.update();
    },
    destroy() {
        this.scroller?.removeEventListener('scroll', this.onScroll);
        this.$el.removeEventListener('click', this.onClick);
    },
    update() {
        if (!this.sections.length || !this.scroller) {
            return;
        }

        const line = this.$el.getBoundingClientRect().bottom + 96;
        const passed = this.sections.filter((section) => section.getBoundingClientRect().top <= line);
        const atEnd = this.scroller.scrollTop + this.scroller.clientHeight >= this.scroller.scrollHeight - 4;
        const next = atEnd ? this.sections.at(-1) : (passed.at(-1) ?? this.sections[0]);

        if (next && next.id !== this.current) {
            this.current = next.id;
            this.reveal();
        }
    },
    reveal(link = null) {
        this.$nextTick(() => {
            const activeLink = link ?? this.$el.querySelector(`a[href="#${this.current}"]`);
            if (activeLink && activeLink.parentElement.scrollWidth > activeLink.parentElement.clientWidth) {
                activeLink.parentElement.scrollTo({
                    left: activeLink.offsetLeft - 16,
                    behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
                });
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
    galleryPhoto: 0,
    dragging: false,
    draggingGallery: false,
    suppressGalleryClick: false,
    dragX: 0,
    mobile: window.matchMedia('(width <= 639px)').matches,
    init() {
        const mobileBreakpoint = window.matchMedia('(width <= 639px)');
        this.mobile = mobileBreakpoint.matches;
        mobileBreakpoint.addEventListener('change', (event) => {
            this.mobile = event.matches;
        });
    },
    isMobileViewport() {
        return this.mobile;
    },
    photoCount(index = this.active) {
        const activeSlide = this.$el.querySelectorAll('.uh-showcase-slide')[index];

        return activeSlide?.querySelectorAll('.uh-showcase-media img').length ?? 0;
    },
    photoOn(index, photoIndex, total) {
        if (window.matchMedia('(width <= 639px)').matches) {
            return index === this.active && photoIndex === this.galleryPhoto;
        }

        return photoIndex === (this.hovered === index && total > 1 ? 1 : 0);
    },
    setPhoto(index, photoIndex, total) {
        if (index !== this.active || total < 1) {
            return;
        }

        this.galleryPhoto = (photoIndex + total) % total;
    },
    previousPhoto() {
        const total = this.photoCount();
        if (total > 1) {
            this.galleryPhoto = (this.galleryPhoto - 1 + total) % total;
        }
    },
    nextPhoto() {
        const total = this.photoCount();
        if (total > 1) {
            this.galleryPhoto = (this.galleryPhoto + 1) % total;
        }
    },
    preventGalleryClick(event) {
        if (!this.suppressGalleryClick || !event.target.closest('.uh-showcase-image-link')) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        this.suppressGalleryClick = false;
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
        this.galleryPhoto = 0;
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
        this.draggingGallery = this.isMobileViewport() && event.target.closest('.uh-showcase-media') !== null;
        this.dragX = event.clientX;
    },
    dragEnd(event) {
        if (!this.dragging) {
            return;
        }
        this.dragging = false;
        const distance = event.clientX - this.dragX;
        const draggingGallery = this.draggingGallery;
        this.draggingGallery = false;
        if (Math.abs(distance) > 40) {
            if (draggingGallery) {
                this.suppressGalleryClick = true;
                window.setTimeout(() => {
                    this.suppressGalleryClick = false;
                }, 400);
                distance < 0 ? this.nextPhoto() : this.previousPhoto();
            } else {
                distance < 0 ? this.next() : this.previous();
            }
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
    presets: [
        {
            name: 'Classic Haven',
            desc: 'Limestone canvas, crisp surface, forest & warm brass',
            brand: {
                primary_1: '#f3f0ea',
                primary_2: '#ffffff',
                secondary_1: '#0d1110',
                secondary_2: '#6e675f',
                accent_1: '#1a3328',
                accent_2: '#c3a06a',
            },
        },
        {
            name: 'Architectural Slate',
            desc: 'Alabaster, pure white, midnight & cobalt blue',
            brand: {
                primary_1: '#f8f9fa',
                primary_2: '#ffffff',
                secondary_1: '#141824',
                secondary_2: '#6c757d',
                accent_1: '#1d4ed8',
                accent_2: '#d97706',
            },
        },
        {
            name: 'Warm Terracotta',
            desc: 'Sand dune, warm ivory, espresso & rich terracotta',
            brand: {
                primary_1: '#faf5ee',
                primary_2: '#fffdf9',
                secondary_1: '#1c1917',
                secondary_2: '#78716c',
                accent_1: '#c2410c',
                accent_2: '#ca8a04',
            },
        },
        {
            name: 'Modern Monochrome',
            desc: 'Chalk canvas, jet black, emerald & champagne',
            brand: {
                primary_1: '#f1f3f5',
                primary_2: '#ffffff',
                secondary_1: '#111111',
                secondary_2: '#555555',
                accent_1: '#064e3b',
                accent_2: '#d4af37',
            },
        },
        {
            name: 'Coastal Haven',
            desc: 'Sea salt, pure white, deep navy & ocean cyan',
            brand: {
                primary_1: '#f0f4f8',
                primary_2: '#ffffff',
                secondary_1: '#0f172a',
                secondary_2: '#64748b',
                accent_1: '#0284c7',
                accent_2: '#ea580c',
            },
        },
    ],
    applyPreset(preset) {
        Object.entries(preset.brand).forEach(([key, val]) => {
            this.setBrand(key, val);
        });
        this.success = `Applied "${preset.name}" preset. Remember to save colors.`;
    },
    setBrand(key, value) {
        const val = (value || '').toLowerCase();
        this.theme.brand[key] = val;
        if (key === 'primary_1') this.theme.brand.dominant = val;
        if (key === 'secondary_1') this.theme.brand.secondary = val;
        if (key === 'accent_1') this.theme.brand.accent = val;
        this.clearMessages();
    },
    setAdvanced(group, key, value) {
        this.theme.advanced[group][key] = (value || '').toLowerCase();
        this.clearMessages();
    },
    pickerColor(value, fallback) {
        return /^#[0-9a-f]{6}$/i.test(value || '') ? value : fallback;
    },
    pickerFallback(group, key) {
        const brand = this.theme.brand || {};
        const fallbacks = {
            surfaces_text: {
                page_background: 'primary_1',
                card_surface: 'primary_2',
                primary_text: 'secondary_1',
                muted_text: 'secondary_2',
                border: 'secondary_2',
            },
            hero_slider: {
                overlay_color: 'secondary_1',
                slider_title: 'secondary_1',
                slider_subtitle: 'secondary_2',
                carousel_arrow: 'accent_1',
                carousel_dot: 'accent_1',
            },
            mobile_nav: {
                drawer_background: 'primary_2',
                active_tab_highlight: 'accent_1',
                badge_fill: 'accent_2',
            },
        };

        const target = fallbacks[group]?.[key] || 'primary_1';
        return brand[target] || brand.dominant || '#ffffff';
    },
    fieldError(key) {
        return this.errors[key]?.[0] || '';
    },
    clearMessages() {
        this.error = '';
        this.success = '';
        this.errors = {};
    },
    restoreUnsavedChanges() {
        window.dispatchEvent(new CustomEvent('uh:save-reset', {
            detail: { formId: this.$root.id },
        }));
    },
    resetGroup(group) {
        Object.keys(this.theme.advanced[group]).forEach((key) => {
            this.theme.advanced[group][key] = '';
        });
        this.clearMessages();
    },
    previewVariables() {
        const brand = this.theme.brand || {};
        const groups = this.theme.advanced || {};
        const surfaces = groups.surfaces_text || {};
        const hero = groups.hero_slider || {};
        const mobile = groups.mobile_nav || {};

        const p1 = brand.primary_1 || brand.dominant || '#f3f0ea';
        const p2 = brand.primary_2 || '#ffffff';
        const s1 = brand.secondary_1 || brand.secondary || '#0d1110';
        const s2 = brand.secondary_2 || '#6e675f';
        const a1 = brand.accent_1 || brand.accent || '#1a3328';
        const a2 = brand.accent_2 || '#c3a06a';

        return {
            '--color-primary-1': p1,
            '--color-primary-2': p2,
            '--color-secondary-1': s1,
            '--color-secondary-2': s2,
            '--color-accent-1': a1,
            '--color-accent-2': a2,
            '--color-dominant': p1,
            '--color-secondary': s1,
            '--color-accent': a1,
            '--color-page-background': surfaces.page_background || p1,
            '--color-card-surface': surfaces.card_surface || p2,
            '--color-body-text': surfaces.primary_text || s1,
            '--color-muted-text': surfaces.muted_text || s2,
            '--color-divider': surfaces.border || `color-mix(in srgb, ${s2} 30%, ${p1})`,
            '--color-hero-overlay': hero.overlay_color || s1,
            '--color-hero-overlay-opacity': hero.overlay_opacity === '' || hero.overlay_opacity === undefined ? '0.3' : hero.overlay_opacity,
            '--color-slider-title': hero.slider_title || 'var(--color-body-text)',
            '--color-slider-subtitle': hero.slider_subtitle || 'var(--color-muted-text)',
            '--color-carousel-arrow': hero.carousel_arrow || a1,
            '--color-carousel-dot': hero.carousel_dot || a1,
            '--color-mobile-nav-background': mobile.drawer_background || p2,
            '--color-mobile-active-highlight': mobile.active_tab_highlight || a1,
            '--color-notification-badge': mobile.badge_fill || a2,
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
                this.restoreUnsavedChanges();
                return;
            }

            window.location.reload();
        } catch {
            this.error = 'Could not save colors. Check your connection and try again.';
            this.restoreUnsavedChanges();
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
    submit(event) {
        if (this.$el.dataset.uploadPendingBypass === 'true') {
            return;
        }

        if (this.submitting) {
            event.preventDefault();
            return;
        }

        this.submitting = true;
        this.$el.dataset.saveInProgress = 'true';
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
Alpine.data('uhPostCreateForm', () => ({
    submitting: false,
    photos: [],
    dragging: false,
    photoLimitExceeded: false,

    selectPhotos(event) {
        const newFiles = [...(event.target.files ?? [])];
        this.addPhotos(newFiles);
    },

    dropPhotos(event) {
        this.dragging = false;
        const droppedFiles = [...(event.dataTransfer?.files ?? [])]
            .filter((file) => ['image/jpeg', 'image/png', 'image/webp'].includes(file.type));

        this.addPhotos(droppedFiles);
    },

    addPhotos(newFiles) {
        if (! newFiles || newFiles.length === 0) return;
        const existingKeys = new Set(this.photos.map((p) => `${p.name}_${p.size}`));
        const uniqueFiles = newFiles.filter((f) => ! existingKeys.has(`${f.name}_${f.size}`));

        this.photoLimitExceeded = (this.photos.length + uniqueFiles.length) > 100;
        const allowed = uniqueFiles.slice(0, Math.max(0, 100 - this.photos.length));

        const newPhotoObjects = allowed.map((file, index) => ({
            id: `article-photo-${Date.now()}-${index}-${Math.random().toString(36).slice(2, 8)}`,
            file,
            name: file.name,
            size: file.size,
            previewUrl: URL.createObjectURL(file),
            altText: '',
        }));

        this.photos = [...this.photos, ...newPhotoObjects];
        this.syncFileInput();
    },

    setPhotos(files) {
        this.revokePreviews();
        this.photoLimitExceeded = files.length > 100;
        this.photos = files.slice(0, 100).map((file, index) => ({
            id: `article-photo-${index}-${Math.random().toString(36).slice(2, 8)}`,
            file,
            name: file.name,
            size: file.size,
            previewUrl: URL.createObjectURL(file),
            altText: '',
        }));
        this.syncFileInput();
    },

    movePhoto(index, direction) {
        const targetIndex = index + direction;
        if (targetIndex < 0 || targetIndex >= this.photos.length) {
            return;
        }

        [this.photos[index], this.photos[targetIndex]] = [this.photos[targetIndex], this.photos[index]];
        this.syncFileInput();
    },

    removePhoto(index) {
        const [photo] = this.photos.splice(index, 1);
        if (photo?.previewUrl) {
            URL.revokeObjectURL(photo.previewUrl);
        }

        this.photoLimitExceeded = false;
        this.syncFileInput();
    },

    syncFileInput() {
        if (! this.$refs.photoInput) {
            return;
        }

        const transfer = new DataTransfer();
        this.photos.forEach((photo) => transfer.items.add(photo.file));
        this.$refs.photoInput.files = transfer.files;
    },

    formatPhotoSize(bytes) {
        if (bytes >= 1048576) {
            return `${(bytes / 1048576).toFixed(1)} MB`;
        }

        return `${Math.max(1, Math.round(bytes / 1024))} KB`;
    },

    revokePreviews() {
        this.photos.forEach((photo) => URL.revokeObjectURL(photo.previewUrl));
    },

    destroy() {
        this.revokePreviews();
    },

    submit(event) {
        if (this.photoLimitExceeded) {
            event.preventDefault();
            return;
        }

        const submitter = event.submitter;
        if (submitter?.name && submitter.value) {
            const submitAction = document.createElement('input');
            submitAction.type = 'hidden';
            submitAction.name = submitter.name;
            submitAction.value = submitter.value;
            this.$el.append(submitAction);
        }

        this.submitting = true;
    },
}));

Alpine.data('uhMediaUpload', (config = {}) => ({
    ownerType: config.ownerType || '',
    ownerId: config.ownerId || 0,
    collection: config.collection || 'gallery',
    reorderUrl: config.reorderUrl || '',
    batchUrl: config.batchUrl || '',
    bulkDeleteUrl: config.bulkDeleteUrl || '',
    coverId: config.coverId || null,
    itemIds: Array.isArray(config.itemIds) ? [...config.itemIds] : [],
    selectedMediaIds: [],
    deletingSelected: false,
    itemsData: {},
    pendingFiles: [],
    submitting: false,
    batchSubmitting: false,
    orderChanged: false,
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
            if (this.$el.closest('form')?.dataset.saveInProgress === 'true') {
                return;
            }

            if (this.hasUnsavedChanges || (this.pendingFiles.some((file) => ! file.uploaded) && ! this.submitting)) {
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

    toggleMediaSelection(id, selected) {
        const selectedIds = new Set(this.selectedMediaIds);
        if (selected) {
            selectedIds.add(id);
        } else {
            selectedIds.delete(id);
        }
        this.selectedMediaIds = [...selectedIds];
    },

    selectAllMedia() {
        this.selectedMediaIds = [...this.itemIds];
    },

    confirmBulkDelete(event) {
        const count = this.selectedMediaIds.length;
        if (count === 0 || ! window.confirm(`Remove ${count} selected ${count === 1 ? 'image' : 'images'}?`)) {
            event.preventDefault();
            return;
        }
        this.deletingSelected = true;
    },

    pick() {
        this.$refs.file?.click();
    },

    addFiles(newFiles, autoSubmit = false) {
        if (! newFiles || newFiles.length === 0) {
            return;
        }

        const input = this.$refs.file;
        const accepted = (input?.accept ?? '')
            .split(',')
            .map((t) => t.trim().toLowerCase())
            .filter(Boolean);

        const validNewFiles = newFiles.filter((file) => {
            if (accepted.length === 0) return true;
            return accepted.some((type) => {
                if (type.startsWith('.')) {
                    return file.name.toLowerCase().endsWith(type);
                }
                if (type.endsWith('/*')) {
                    const prefix = type.replace('/*', '');
                    return file.type.startsWith(prefix);
                }
                return file.type === type;
            });
        });

        if (validNewFiles.length === 0) {
            return;
        }

        const isMultiple = Boolean(input?.multiple);

        if (! isMultiple) {
            this.pendingFiles.forEach((pf) => pf.previewUrl && URL.revokeObjectURL(pf.previewUrl));
            this.pendingFiles = [];
        }

        const existingKeys = new Set(this.pendingFiles.map((pf) => `${pf.name}_${pf.size}`));
        const filteredNewFiles = isMultiple
            ? validNewFiles.filter((f) => ! existingKeys.has(`${f.name}_${f.size}`))
            : validNewFiles;

        const newPending = filteredNewFiles.map((file, idx) => ({
            id: 'pf_' + Date.now() + '_' + idx + '_' + Math.random().toString(36).substring(2, 7),
            file: file,
            name: file.name,
            size: file.size,
            sizeFormatted: this.formatSize(file.size),
            previewUrl: file.type?.startsWith('image/') ? URL.createObjectURL(file) : null,
            isImage: file.type?.startsWith('image/') ?? false,
            alt_text: '',
            is_public: true,
            make_cover: false,
            uploaded: false,
        }));

        if (isMultiple) {
            this.pendingFiles = [...this.pendingFiles, ...newPending];
        } else {
            this.pendingFiles = newPending;
        }

        this.syncInputFiles();

        if (autoSubmit && this.pendingFiles.length > 0 && input?.form) {
            this.uploadPending();
        }
    },

    syncInputFiles() {
        const input = this.$refs.file;
        const unUploaded = this.pendingFiles.filter((pf) => ! pf.uploaded);

        if (input) {
            try {
                const transfer = new DataTransfer();
                unUploaded.forEach((pf) => {
                    if (pf.file instanceof File) {
                        transfer.items.add(pf.file);
                    }
                });
                input.files = transfer.files;
            } catch (e) {
                // Ignore if DataTransfer is not available in environment
            }
        }

        const totalBytes = unUploaded.reduce((total, item) => total + item.size, 0);
        this.filename = unUploaded.length > 1 ? `${unUploaded.length} files` : (unUploaded[0]?.name ?? '');
        this.filesize = unUploaded.length > 0 ? this.formatSize(totalBytes) : '';
        this.previews = unUploaded.filter((pf) => pf.previewUrl).slice(0, 12).map((pf) => pf.previewUrl);
        this.preview = this.previews[0] ?? '';
    },

    dropped(event, autoSubmit = false) {
        this.dragging = false;
        const files = [...(event.dataTransfer?.files ?? [])];
        if (files.length === 0) return;
        this.addFiles(files, autoSubmit);
    },

    chosen(autoSubmit = false) {
        const files = [...(this.$refs.file?.files ?? [])];
        if (files.length === 0) return;
        this.addFiles(files, autoSubmit);
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
        this.syncInputFiles();

        if (this.pendingFiles.length === 0) {
            this.clear();
        }
    },

    uploadPending() {
        if (! this.$refs.file?.form || ! this.pendingFiles.some((file) => ! file.uploaded)) {
            return;
        }
        this.uploadPendingFiles({ reloadOnSuccess: true });
    },

    setPendingCover(index) {
        this.pendingFiles.forEach((file, fileIndex) => {
            file.make_cover = fileIndex === index;
        });
    },

    appendPendingMetadata(formData) {
        const unUploaded = this.pendingFiles.filter((file) => ! file.uploaded);
        unUploaded.forEach((file, index) => {
            if (! file.isImage) {
                return;
            }

            formData.append(`files_meta[${index}][alt_text]`, file.alt_text ?? '');
            formData.append(`files_meta[${index}][is_public]`, '1');
            formData.append(`files_meta[${index}][make_cover]`, file.make_cover ? '1' : '0');
        });
    },

    buildUploadFormData(form) {
        const formData = new FormData(form);
        const unUploaded = this.pendingFiles.filter((file) => ! file.uploaded);

        if (unUploaded.length === 0) {
            return formData;
        }

        const input = this.$refs.file;
        const fileField = input?.multiple ? (input.name || 'files[]') : (input?.name || 'file');
        ['file', 'files', 'files[]'].forEach((field) => formData.delete(field));
        unUploaded.forEach((pendingFile) => {
            formData.append(fileField, pendingFile.file, pendingFile.name);
        });
        this.appendPendingMetadata(formData);

        return formData;
    },

    validatePendingSettings() {
        return true;
    },

    insertUploadedCards(records) {
        if (! records || records.length === 0) return;
        const grid = document.getElementById('media-grid-' + this.collection);
        if (! grid) return;

        const section = grid.closest('.uh-admin-media');
        const emptyNotice = section?.querySelector('.uh-admin-media-empty');
        if (emptyNotice) {
            emptyNotice.remove();
        }

        const isDoc = this.collection === 'brochure';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || document.querySelector('input[name="_token"]')?.value
            || '';

        records.forEach((record) => {
            if (! record || ! record.id) return;

            if (! this.itemIds.includes(record.id)) {
                this.itemIds.push(record.id);
            }
            this.itemsData[record.id] = {
                alt_text: '',
                is_public: Boolean(record.is_public),
                make_cover: this.coverId === record.id,
            };

            if (document.getElementById('media-item-' + record.id)) {
                return;
            }

            const li = document.createElement('li');
            li.className = 'uh-admin-media-card relative';
            li.id = 'media-item-' + record.id;

            const filename = record.original_filename || 'Uploaded file';
            const sizeFormatted = record.size_bytes ? this.formatSize(record.size_bytes) : '';
            const previewHtml = isDoc
                ? `<span class="uh-admin-media-slot"><svg class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg></span>`
                : `<img src="${record.thumb_url || record.url}" alt="" loading="lazy" decoding="async">`;

            li.innerHTML = `
                ${!isDoc ? `<label class="absolute left-2 top-2 z-10 flex size-7 cursor-pointer items-center justify-center rounded-md bg-white/95 shadow" title="Select image">
                    <input type="checkbox" class="size-4" aria-label="Select image" onchange="window.Alpine?.$data(this.closest('.uh-admin-media'))?.toggleMediaSelection(${record.id}, this.checked)">
                </label>` : ''}
                <div class="uh-admin-media-card-bar">
                    <div class="uh-admin-media-card-copy">
                        <p class="uh-admin-media-name" title="${filename}">${filename}</p>
                        <p class="uh-admin-media-size">${sizeFormatted}</p>
                    </div>
                    <div class="flex items-center gap-1.5"></div>
                    <div class="flex items-center gap-0.5 border-l border-white/20 pl-1">
                        <button type="button" class="uh-icon-btn size-6 text-white/80 hover:text-white disabled:opacity-25"
                                aria-label="Move earlier" title="Move earlier" onclick="window.Alpine?.$data(this.closest('.uh-admin-media'))?.moveItem(${record.id}, -1)">
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
                        </button>
                        <button type="button" class="uh-icon-btn size-6 text-white/80 hover:text-white disabled:opacity-25"
                                aria-label="Move later" title="Move later" onclick="window.Alpine?.$data(this.closest('.uh-admin-media'))?.moveItem(${record.id}, 1)">
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
                        </button>
                        <form method="POST" action="/admin/media/${record.id}" onsubmit="return confirm('Remove this ${isDoc ? 'file' : 'photograph'}?');">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="uh-admin-media-remove ml-1" aria-label="Remove ${filename}">
                                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="uh-admin-media-preview">
                    ${previewHtml}
                </div>
            `;
            grid.appendChild(li);
        });

        const headingEl = section?.querySelector('.uh-admin-media-heading h2');
        if (headingEl) {
            headingEl.textContent = headingEl.textContent.replace(/\(\d+\)/, `(${this.itemIds.length})`);
        }
    },

    async uploadPendingFiles({ reloadOnSuccess = false } = {}) {
        const form = this.$refs.file?.form;
        const unUploaded = this.pendingFiles.filter((file) => ! file.uploaded);
        if (! form || unUploaded.length === 0) {
            return false;
        }

        if (! this.validatePendingSettings()) {
            return false;
        }

        this.submitting = true;
        const formData = this.buildUploadFormData(form);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: formData,
                credentials: 'same-origin',
            });
            const payload = await response.json().catch(() => ({}));
            if (! response.ok) {
                const validationMessage = Object.values(payload.errors ?? {}).flat()[0];
                throw new Error(validationMessage || payload.message || 'The selected file could not be uploaded.');
            }

            const uploadedRecords = payload.records ?? (payload.id ? [payload] : []);
            this.insertUploadedCards(uploadedRecords);
            this.clear();

            const count = uploadedRecords.length || 1;
            const noun = this.collection === 'brochure' ? (count === 1 ? 'file' : 'files') : (count === 1 ? 'photograph' : 'photographs');
            this.flashNotice(`${count} ${noun} uploaded successfully.`, 'success');

            return true;
        } catch (error) {
            this.submitting = false;
            this.flashNotice(error instanceof Error ? error.message : 'The selected file could not be uploaded.', 'danger');

            return false;
        } finally {
            this.submitting = false;
        }
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

    submit(event) {
        event.preventDefault();
        this.uploadPendingFiles({ reloadOnSuccess: true });
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

        this.orderChanged = true;
        this.hasUnsavedChanges = true;
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

    async saveAllDetails() {
        if (! this.batchUrl) {
            return false;
        }

        if (! this.hasUnsavedChanges) {
            return true;
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
        try {
            const response = await fetch(this.batchUrl, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ items: itemsPayload }),
            });
            const data = await response.json();

            if (! response.ok || ! data.ok) {
                throw new Error(data.message || 'Failed to save image changes.');
            }

            if (this.orderChanged && this.reorderUrl) {
                const reorderResponse = await fetch(this.reorderUrl, {
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
                });

                if (! reorderResponse.ok) {
                    throw new Error('Failed to save image order.');
                }
            }

            this.hasUnsavedChanges = false;
            this.orderChanged = false;
            this.flashNotice('Image settings saved.', 'success');
            return true;
        } catch (error) {
            this.batchSubmitting = false;
            this.flashNotice(error instanceof Error ? error.message : 'Network error while saving.', 'danger');
            return false;
        } finally {
            this.batchSubmitting = false;
        }
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

Alpine.data('uhDeleteConfirmation', (title = '') => ({
    title,
    form: null,
    submitting: false,
    approved: false,
    guard(event) {
        if (this.approved) {
            this.approved = false;
            return;
        }

        event.preventDefault();

        if (this.submitting || this.$refs.dialog.open) {
            return;
        }

        this.form = event.currentTarget;
        this.$refs.dialog.showModal();
    },
    cancel() {
        if (this.$refs.dialog.open) {
            this.$refs.dialog.close();
        }

        this.form = null;
    },
    confirm() {
        if (! this.form || this.submitting) {
            return;
        }

        this.submitting = true;
        this.approved = true;
        this.$refs.dialog.close();
        this.form.requestSubmit();
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

const bootPendingMediaUploads = () => {
    document.querySelectorAll('form[data-upload-pending-media]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            if (form.dataset.uploadPendingBypass === 'true') {
                delete form.dataset.uploadPendingBypass;
                return;
            }

            if (form.dataset.uploadingPendingMedia === 'true') {
                event.preventDefault();
                return;
            }

            const uploadForms = [...document.querySelectorAll('form.uh-admin-media-upload')]
                .filter((uploadForm) => {
                    const section = uploadForm.closest('.uh-admin-media');
                    const mediaState = section ? window.Alpine?.$data(section) : null;
                    const hasPendingFiles = mediaState?.pendingFiles?.some((file) => ! file.uploaded);

                    return hasPendingFiles || uploadForm.querySelector('input[type="file"]')?.files.length > 0;
                });
            const mediaStates = [...document.querySelectorAll('.uh-admin-media[x-data]')]
                .map((section) => window.Alpine?.$data(section))
                .filter(Boolean);
            const dirtyMediaStates = mediaStates.filter((mediaState) => mediaState.hasUnsavedChanges);

            if (uploadForms.length === 0 && dirtyMediaStates.length === 0) {
                return;
            }

            event.preventDefault();
            form.dataset.uploadingPendingMedia = 'true';
            const submitter = event.submitter;
            let activeUploadForm = null;

            try {
                for (const mediaState of dirtyMediaStates) {
                    if (! await mediaState.saveAllDetails()) {
                        throw new Error(mediaState.statusNotice || 'Please fix the image settings before saving.');
                    }
                }

                for (const uploadForm of uploadForms) {
                    activeUploadForm = uploadForm;
                    const section = uploadForm.closest('.uh-admin-media');
                    const mediaState = section ? window.Alpine?.$data(section) : null;
                    if (mediaState && ! mediaState.validatePendingSettings()) {
                        throw new Error(mediaState.statusNotice || 'Please complete the selected image settings.');
                    }
                    if (mediaState) {
                        mediaState.submitting = true;
                    }

                    const uploadData = mediaState
                        ? mediaState.buildUploadFormData(uploadForm)
                        : new FormData(uploadForm);

                    const response = await fetch(uploadForm.action, {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: uploadData,
                        credentials: 'same-origin',
                    });
                    const payload = await response.json().catch(() => ({}));

                    if (! response.ok) {
                        const validationMessage = Object.values(payload.errors ?? {}).flat()[0];
                        throw new Error(validationMessage || payload.message || 'The selected file could not be uploaded.');
                    }

                    if (mediaState) {
                        mediaState.clear();
                    } else {
                        uploadForm.reset();
                    }
                }

                form.dataset.uploadPendingBypass = 'true';
                delete form.dataset.uploadingPendingMedia;
                if (submitter) {
                    form.requestSubmit(submitter);
                } else {
                    form.requestSubmit();
                }
            } catch (error) {
                const message = error instanceof Error ? error.message : 'The selected file could not be uploaded.';
                const section = activeUploadForm?.closest('.uh-admin-media');
                const mediaState = section ? window.Alpine?.$data(section) : null;
                if (mediaState) {
                    mediaState.submitting = false;
                    mediaState.flashNotice(message, 'danger');
                }
                delete form.dataset.uploadingPendingMedia;
                delete form.dataset.saveInProgress;
                const formState = window.Alpine?.$data(form);
                if (formState && 'submitting' in formState) {
                    formState.submitting = false;
                }
                window.dispatchEvent(new CustomEvent('uh:save-reset', { detail: { formId: form.id } }));
            }
        });
    });
};

/**
 * Admin forms marked data-unsaved-guard warn before the page is left with edits that were never saved.
 */
const bootUnsavedGuards = () => {
    if (! document.body.classList.contains('uh-admin')) {
        return;
    }

    let isTrackingReady = false;
    setTimeout(() => { isTrackingReady = true; }, 1000);

    const dirty = new Set();

    document.querySelectorAll('form[data-unsaved-guard]').forEach((form) => {
        const mark = (e) => {
            if (isTrackingReady) {
                if (e.target.classList && e.target.classList.contains('select2-search__field')) {
                    return;
                }
                dirty.add(form);
            }
        };
        form.addEventListener('input', mark);
        form.addEventListener('change', mark);
        form.addEventListener('submit', () => {
            // Submitting is an explicit save attempt. Clear this form before the browser
            // starts a native navigation or an Alpine handler starts an async save.
            dirty.delete(form);
        });
    });

    window.addEventListener('uh:save-reset', (event) => {
        const form = event.detail?.formId
            ? document.getElementById(event.detail.formId)
            : null;
        if (form?.matches('form[data-unsaved-guard]')) {
            dirty.add(form);
        }
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
    if (document.body.classList.contains('uh-admin')) {
        syncAdminMobileTables();
    }
    bootAdminSelects();
    bootPendingMediaUploads();
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
