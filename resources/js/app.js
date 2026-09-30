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
    more: hasAdvanced,
    showMap: window.matchMedia('(min-width: 1024px)').matches,
    layout: 'list',
    preview: null,
    slide: 0,
    init() {
        try {
            const stored = localStorage.getItem('uh-browse-layout');
            if (stored === 'grid' || stored === 'list') {
                this.layout = stored;
            }
        } catch {
            this.layout = 'list';
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
        const mobileFilters = window.matchMedia('(max-width: 1023px)').matches && this.filtersOpen;
        document.body.style.overflow = mobileFilters || this.preview ? 'hidden' : '';
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
