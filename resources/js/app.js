import Alpine from 'alpinejs';
import { track, bootAnalytics, bindTrackedClicks } from './analytics';
import { registerSavedStore } from './saved';
import { bootMaps, refreshMaps } from './maps';

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
    showMap: window.matchMedia('(min-width: 1024px)').matches,
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
 * Homepage sale/rent section tabs.
 */
Alpine.data('uhHomeTabs', (initial = 'sale') => ({
    tab: initial,
}));

/**
 * Homepage hero search: purpose toggle and a dual-thumb price range.
 */
Alpine.data('uhHeroSearch', (initial = 'sale') => ({
    purpose: initial,
    min: 0,
    max: 480_000_000,
    submitting: false,
    saleCeiling: 480_000_000,
    rentCeiling: 500_000,
    init() {
        this.max = this.ceiling;
    },
    get ceiling() {
        return this.purpose === 'rent' ? this.rentCeiling : this.saleCeiling;
    },
    get step() {
        return this.purpose === 'rent' ? 1000 : 100_000;
    },
    get fillStyle() {
        const span = this.ceiling || 1;
        const start = (Math.max(0, Math.min(this.min, this.ceiling)) / span) * 100;
        const end = (Math.max(0, Math.min(this.max, this.ceiling)) / span) * 100;

        return `left:${start}%;width:${Math.max(0, end - start)}%;`;
    },
    setPurpose(next) {
        this.purpose = next;
        this.min = 0;
        this.max = this.ceiling;
    },
    clampMin() {
        this.min = Math.max(0, Math.min(Number(this.min) || 0, this.max));
    },
    clampMax() {
        this.max = Math.max(this.min, Math.min(Number(this.max) || 0, this.ceiling));
    },
    submit() {
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
    init() {
        this.token = this.$el.querySelector('input[name="submission_token"]')?.value || newToken();
        this.syncToken();
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

registerSavedStore(Alpine);

window.Alpine = Alpine;
window.uhTrack = track;
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    bootMaps();
    bindTrackedClicks();

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
