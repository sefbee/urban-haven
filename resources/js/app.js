import Alpine from 'alpinejs';
import L from 'leaflet';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerRetina from 'leaflet/dist/images/marker-icon-2x.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconUrl: markerIcon,
    iconRetinaUrl: markerRetina,
    shadowUrl: markerShadow,
});

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
        this.$nextTick(() => window.dispatchEvent(new Event('uh:refresh-maps')));
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
Alpine.data('uhHeroSearch', () => ({
    purpose: 'sale',
    min: 0,
    max: 480_000_000,
    submitting: false,
    saleCeiling: 480_000_000,
    rentCeiling: 500_000,
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

window.Alpine = Alpine;
window.L = L;
Alpine.start();

/**
 * Boot every [data-uh-map] element on the page. Markers are optional, so the
 * same component serves the search results map and a single property location.
 */
const maps = [];

const bootMaps = () => {
    document.querySelectorAll('[data-uh-map]').forEach((element) => {
        if (element.dataset.uhMapReady === 'true') {
            return;
        }
        element.dataset.uhMapReady = 'true';

        const lat = Number(element.dataset.lat || 23.8103);
        const lng = Number(element.dataset.lng || 90.4125);
        const zoom = Number(element.dataset.zoom || 12);

        const map = L.map(element, {
            scrollWheelZoom: false,
            zoomControl: true,
        }).setView([lat, lng], zoom);

        L.tileLayer(element.dataset.tiles, {
            attribution: element.dataset.attribution,
            maxZoom: 19,
        }).addTo(map);

        map.on('focus', () => map.scrollWheelZoom.enable());
        map.on('blur', () => map.scrollWheelZoom.disable());

        let points = [];
        try {
            points = JSON.parse(element.dataset.properties || '[]');
        } catch {
            points = [];
        }

        if (element.dataset.radius) {
            L.circle([lat, lng], {
                radius: Number(element.dataset.radius),
                color: '#2f5a43',
                weight: 1,
                fillColor: '#2f5a43',
                fillOpacity: 0.12,
            }).addTo(map);
        }

        const markers = points.map((point) => {
            const popup = [
                `<strong>${escapeHtml(point.title)}</strong>`,
                escapeHtml(point.price),
                `<a href="${escapeHtml(point.url)}">View details</a>`,
            ].join('<br>');

            return L.marker([point.lat, point.lng]).addTo(map).bindPopup(popup);
        });

        if (markers.length > 1) {
            map.fitBounds(L.featureGroup(markers).getBounds().pad(0.2), {
                animate: !prefersReducedMotion(),
            });
        }

        maps.push(map);
    });
};

document.addEventListener('DOMContentLoaded', bootMaps);

/** Maps rendered inside a hidden container need a nudge once revealed. */
window.addEventListener('uh:refresh-maps', () => {
    maps.forEach((map) => map.invalidateSize());
});
