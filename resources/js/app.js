import Alpine from 'alpinejs';
import { track, bootAnalytics, bindTrackedClicks } from './analytics';
import { applySelectOption, bootAdminSelects } from './admin-selects';
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
 * Listing browse: filter drawer, list or card layout, map, and quick view.
 */
Alpine.data('uhBrowse', (hasAdvanced = false) => ({
    filtersOpen: false,
    submitting: false,
    more: hasAdvanced,
    showMap: window.matchMedia('(min-width: 1024px)').matches
        || new URLSearchParams(window.location.search).get('view') === 'map',
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
    toggleMap() {
        this.showMap = !this.showMap;
        this.$nextTick(() => {
            bootMaps();
            refreshMaps();
        });
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
    onScroll: null,
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
        window.addEventListener('scroll', this.onScroll, { passive: true });
        this.update();
    },
    destroy() {
        window.removeEventListener('scroll', this.onScroll);
    },
    update() {
        const line = window.innerHeight * 0.3;
        const passed = this.sections.filter((section) =>
            getComputedStyle(section).position !== 'sticky' && section.getBoundingClientRect().top <= line);
        const next = passed.at(-1) ?? this.sections[0];

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
 * Homepage featured carousel: one centred listing with its neighbours receding either side.
 * Wraps in both directions; swipe, arrow keys and clicking a neighbour all move it.
 */
Alpine.data('uhShowcase', (count = 0) => ({
    count,
    active: 0,
    dragging: false,
    dragX: 0,
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
 * Homepage search card. The location field suggests known areas as you type and falls back to a
 * keyword search; the button says how many properties match before anyone submits.
 */
Alpine.data('uhHeroSearch', ({ initial = 'sale', countUrl = null, labels = {}, areas = [], budgets = {} } = {}) => ({
    purpose: initial,
    query: '',
    area: '',
    type: '',
    beds: '',
    min: '',
    max: '',
    suggesting: false,
    highlighted: -1,
    submitting: false,
    matches: null,
    counting: false,
    countTimer: null,
    countRequest: 0,
    init() {
        ['purpose', 'area', 'type', 'beds', 'min', 'max'].forEach((key) => {
            this.$watch(key, () => this.queueCount());
        });
        this.$watch('query', () => {
            if (!this.area) {
                this.queueCount();
            }
        });
        window.addEventListener('pageshow', () => {
            this.submitting = false;
            this.$root.querySelectorAll('input[name], select[name]').forEach((field) => {
                field.disabled = false;
            });
        });
    },
    get refined() {
        return this.query.trim() !== '' || this.type !== '' || this.beds !== '' || this.hasMin || this.hasMax;
    },
    get buttonLabel() {
        if (this.matches === null) {
            return labels.idle;
        }
        if (this.matches === 0) {
            return labels.none;
        }

        return this.matches === 1 ? labels.one : labels.many.replace(':count', this.matches.toLocaleString());
    },
    get suggestions() {
        const needle = this.query.trim().toLowerCase();
        if (this.area || needle === '') {
            return [];
        }

        return areas.filter((option) => option.label.toLowerCase().includes(needle)).slice(0, 6);
    },
    typed() {
        this.area = '';
        this.suggesting = true;
        this.highlighted = -1;
    },
    move(step) {
        const total = this.suggestions.length;
        if (!total) {
            return;
        }
        this.suggesting = true;
        this.highlighted = (this.highlighted + step + total) % total;
    },
    pick(option) {
        this.area = String(option.id);
        this.query = option.label;
        this.suggesting = false;
        this.highlighted = -1;
    },
    pickHighlighted(event) {
        const option = this.suggesting ? this.suggestions[this.highlighted] : null;
        if (option) {
            event.preventDefault();
            this.pick(option);
        }
    },
    get minBudgets() {
        const options = budgets[this.purpose] ?? [];

        return this.max === '' ? options : options.filter((option) => option.value < Number(this.max));
    },
    get maxBudgets() {
        const options = budgets[this.purpose] ?? [];

        return this.min === '' ? options : options.filter((option) => option.value > Number(this.min));
    },
    reset() {
        this.query = '';
        this.area = '';
        this.type = '';
        this.beds = '';
        this.min = '';
        this.max = '';
        this.matches = null;
    },
    queueCount() {
        if (!countUrl) {
            return;
        }
        clearTimeout(this.countTimer);
        this.countTimer = setTimeout(() => this.count(), 280);
    },
    async count() {
        const params = new URLSearchParams({ listing_type: this.purpose });
        if (this.area) {
            params.set('location_area_id', this.area);
        } else if (this.query.trim()) {
            params.set('q', this.query.trim());
        }
        if (this.type) params.set('property_type_id', this.type);
        if (this.beds) params.set('min_beds', this.beds);
        if (this.hasMin) params.set('min_price', this.min);
        if (this.hasMax) params.set('max_price', this.max);

        const request = ++this.countRequest;
        this.counting = true;
        try {
            const response = await fetch(`${countUrl}?${params}`, { headers: { Accept: 'application/json' } });
            if (!response.ok) {
                throw new Error(String(response.status));
            }
            const payload = await response.json();
            if (request === this.countRequest) {
                this.matches = Number(payload.total ?? 0);
            }
        } catch {
            if (request === this.countRequest) {
                this.matches = null;
            }
        } finally {
            if (request === this.countRequest) {
                this.counting = false;
            }
        }
    },
    get hasMin() {
        return this.min !== '' && Number(this.min) > 0;
    },
    get hasMax() {
        return this.max !== '' && Number(this.max) > 0;
    },
    setPurpose(next) {
        this.purpose = next;
        this.min = '';
        this.max = '';
    },
    submit(event) {
        event.target.querySelectorAll('input[name], select[name]').forEach((field) => {
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
 * Chip-style multi-select for Dhaka location areas.
 */
Alpine.data('uhLocationTags', (areas = [], selectedIds = []) => ({
    areas,
    selected: selectedIds.map(String),
    query: '',
    open: false,
    get selectedAreas() {
        return this.areas.filter((area) => this.selected.includes(String(area.id)));
    },
    get suggestions() {
        const query = this.query.trim().toLowerCase();

        return this.areas.filter((area) => {
            if (this.selected.includes(String(area.id))) {
                return false;
            }

            return query === '' || String(area.name).toLowerCase().includes(query);
        }).slice(0, 8);
    },
    add(id) {
        const key = String(id);
        if (!this.selected.includes(key)) {
            this.selected.push(key);
        }
        this.query = '';
        this.open = false;
    },
    remove(id) {
        this.selected = this.selected.filter((value) => value !== String(id));
    },
    onEnter() {
        if (this.suggestions[0]) {
            this.add(this.suggestions[0].id);
        }
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
        const file = this.$refs.file?.files?.[0];
        if (this.preview) {
            URL.revokeObjectURL(this.preview);
        }
        this.filename = file?.name ?? '';
        this.filesize = file ? this.formatSize(file.size) : '';
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
