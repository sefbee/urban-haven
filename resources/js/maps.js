/**
 * Leaflet is loaded only when a page contains a map, and each map boots when it scrolls into view.
 * Search maps read pins from a JSON endpoint that applies the same filters as the result list.
 */
const maps = [];
let leafletPromise = null;

const escapeHtml = (value) =>
    String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

const safeUrl = (value) => (typeof value === 'string' && value.startsWith('/') && !value.startsWith('//')) || /^https?:\/\//.test(value) ? value : '#';

const prefersReducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const loadLeaflet = () => {
    leafletPromise ??= Promise.all([
        import('leaflet'),
        import('leaflet/dist/leaflet.css'),
        import('leaflet/dist/images/marker-icon.png'),
        import('leaflet/dist/images/marker-icon-2x.png'),
        import('leaflet/dist/images/marker-shadow.png'),
    ]).then(([module, , icon, retina, shadow]) => {
        const L = module.default ?? module;
        delete L.Icon.Default.prototype._getIconUrl;
        L.Icon.Default.mergeOptions({ iconUrl: icon.default, iconRetinaUrl: retina.default, shadowUrl: shadow.default });

        return L;
    });

    return leafletPromise;
};

const showMessage = (element, message) => {
    let note = element.parentElement?.querySelector('[data-uh-map-message]');
    if (!note) {
        note = document.createElement('p');
        note.dataset.uhMapMessage = 'true';
        note.className = 'mt-2 text-xs text-[var(--color-muted)]';
        note.setAttribute('role', 'status');
        element.insertAdjacentElement('afterend', note);
    }
    note.textContent = message;
};

const popupFor = (point) => [
    `<strong>${escapeHtml(point.title)}</strong>`,
    point.price ? escapeHtml(point.price) : '',
    point.approximate ? '<em>Approximate location</em>' : '',
    `<a href="${escapeHtml(safeUrl(point.url))}">View details</a>`,
].filter(Boolean).join('<br>');

const plot = (L, map, element, payload) => {
    const layer = L.featureGroup().addTo(map);

    (payload.points || []).forEach((point) => {
        L.marker([point.lat, point.lng], { title: point.title }).bindPopup(popupFor(point)).addTo(layer);
    });

    (payload.clusters || []).forEach((cluster) => {
        if (cluster.count === 1 && cluster.url) {
            L.marker([cluster.lat, cluster.lng], { title: cluster.title }).bindPopup(popupFor(cluster)).addTo(layer);
            return;
        }

        const size = Math.min(56, 30 + Math.log2(cluster.count) * 6);
        L.marker([cluster.lat, cluster.lng], {
            icon: L.divIcon({
                className: 'uh-map-cluster',
                html: `<span>${Number(cluster.count)}</span>`,
                iconSize: [size, size],
            }),
            keyboard: true,
            title: `${cluster.count} properties`,
        })
            .on('click', () => map.setView([cluster.lat, cluster.lng], Math.min(map.getZoom() + 2, 18), { animate: !prefersReducedMotion() }))
            .addTo(layer);
    });

    if (layer.getLayers().length > 1) {
        map.fitBounds(layer.getBounds().pad(0.2), { animate: !prefersReducedMotion(), maxZoom: 16 });
    } else if (layer.getLayers().length === 1) {
        map.setView(layer.getLayers()[0].getLatLng(), 15);
    }

    if ((payload.total ?? 0) === 0 && element.dataset.src) {
        showMessage(element, 'No properties with a map location match these filters.');
    }
};

const boot = async (element) => {
    element.dataset.uhMapReady = 'true';

    let L;
    try {
        L = await loadLeaflet();
    } catch {
        showMessage(element, 'The map could not load. The list still shows every result.');
        return;
    }

    const lat = Number(element.dataset.lat || 23.8103);
    const lng = Number(element.dataset.lng || 90.4125);
    const map = L.map(element, { scrollWheelZoom: false, zoomControl: true }).setView([lat, lng], Number(element.dataset.zoom || 12));

    let tileErrors = 0;
    L.tileLayer(element.dataset.tiles, { attribution: element.dataset.attribution, maxZoom: 19 })
        .on('tileerror', () => {
            tileErrors += 1;
            if (tileErrors === 4) {
                showMessage(element, 'Map tiles are not loading right now. Pins may still be shown.');
            }
        })
        .addTo(map);

    map.on('focus', () => map.scrollWheelZoom.enable());
    map.on('blur', () => map.scrollWheelZoom.disable());

    if (element.dataset.radius) {
        L.circle([lat, lng], { radius: Number(element.dataset.radius), color: '#2f5a43', weight: 1, fillColor: '#2f5a43', fillOpacity: 0.12 }).addTo(map);
    }

    if (element.dataset.approximate === 'true') {
        L.circle([lat, lng], { radius: 600, color: '#2f5a43', weight: 1, fillOpacity: 0.1 }).addTo(map);
        showMessage(element, 'The pin shows the approximate area, not the exact building.');
    } else if (element.dataset.pin === 'true') {
        L.marker([lat, lng]).addTo(map);
    }

    if (element.dataset.src) {
        try {
            const response = await fetch(element.dataset.src, { headers: { Accept: 'application/json' } });
            if (!response.ok) {
                throw new Error(String(response.status));
            }
            plot(L, map, element, await response.json());
        } catch {
            showMessage(element, 'Map pins could not load. The list still shows every result.');
        }
    } else if (element.dataset.properties) {
        try {
            plot(L, map, element, { points: JSON.parse(element.dataset.properties), total: null });
        } catch {
            // Invalid inline data leaves an empty map.
        }
    }

    maps.push(map);
};

export const bootMaps = () => {
    const pending = [...document.querySelectorAll('[data-uh-map]')].filter((element) => element.dataset.uhMapReady !== 'true' && element.offsetParent !== null);

    if (pending.length === 0) {
        return;
    }

    if (!('IntersectionObserver' in window)) {
        pending.forEach(boot);
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                observer.unobserve(entry.target);
                boot(entry.target);
            }
        });
    }, { rootMargin: '200px' });

    pending.forEach((element) => observer.observe(element));
};

export const refreshMaps = () => {
    bootMaps();
    maps.forEach((map) => map.invalidateSize());
};
