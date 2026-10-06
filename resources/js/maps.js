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
        note.className = 'uh-map-note';
        note.setAttribute('role', 'status');
        element.insertAdjacentElement('afterend', note);
    }
    note.textContent = message;
};

const popupFor = (point) => [
    `<strong>${escapeHtml(point.title)}</strong>`,
    point.price ? escapeHtml(point.price) : '',
    point.approximate ? '<em>Approximate location</em>' : '',
    `<a href="${escapeHtml(safeUrl(point.url))}">Explore this property</a>`,
].filter(Boolean).join('<br>');

const svg = (paths) => `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${paths}</svg>`;

const ICONS = {
    bed: svg('<path d="M2 17v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5"/><path d="M2 17h20"/><path d="M6 10V7a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v3"/><path d="M2 20v-3"/><path d="M22 20v-3"/>'),
    bath: svg('<path d="M4 12h16v3a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4v-3Z"/><path d="M7 12V6a2 2 0 0 1 2-2h0a2 2 0 0 1 2 2"/><path d="M7 21v-2"/><path d="M17 21v-2"/>'),
    sofa: svg('<path d="M5 11V8a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v3"/><path d="M3 13a2 2 0 0 1 4 0v2h10v-2a2 2 0 0 1 4 0v4H3z"/><path d="M5 17v2"/><path d="M19 17v2"/>'),
};

/**
 * Pin card: photograph, size, title, place, price and a short facts row. Points from the map
 * endpoint carry their own details; when a matching result card is on the page it lends its photo.
 */
const identityFor = (point) => {
    const card = document.querySelector(`.uh-listing[data-spotlight="${Number(point.id)}"]`);
    const cardImage = card?.querySelector('.uh-listing-frame img');
    const source = point.image ? safeUrl(point.image) : (cardImage ? safeUrl(cardImage.currentSrc || cardImage.src) : '#');
    const place = point.place || card?.querySelector('.uh-listing-place > span:first-child')?.textContent.trim();

    if (!card && !point.image && !point.place) {
        return popupFor(point);
    }

    const facts = [
        point.beds ? `<span>${ICONS.bed}${Number(point.beds)}</span>` : '',
        point.baths ? `<span>${ICONS.bath}${Number(point.baths)}</span>` : '',
        point.furnishing ? `<span>${ICONS.sofa}${escapeHtml(point.furnishing)}</span>` : '',
    ].filter(Boolean).join('');

    return `<a class="uh-pin-card" href="${escapeHtml(safeUrl(point.url))}">${
        source !== '#' ? `<span class="uh-pin-card-media"><img src="${escapeHtml(source)}" alt="" loading="lazy"></span>` : ''
    }<span class="uh-pin-card-body">${
        point.area ? `<span class="uh-pin-card-area">${escapeHtml(point.area)}</span>` : ''
    }<strong class="uh-pin-card-title">${escapeHtml(point.title)}</strong>${
        place ? `<span class="uh-pin-card-place">${escapeHtml(place)}</span>` : ''
    }${point.price ? `<span class="uh-pin-card-price">${escapeHtml(point.price)}</span>` : ''}${
        facts ? `<span class="uh-pin-card-facts">${facts}</span>` : ''
    }${point.approximate ? '<em>Approximate location</em>' : ''}</span></a>`;
};

const MARK = '#d93025';

/**
 * Short pin labels: "BDT 42,000,000" becomes "4.2 Cr", "BDT 85,000 /month" becomes "85K/mo".
 */
const pinLabel = (price) => {
    const amount = Number(String(price ?? '').replace(/[^0-9.]/g, ''));

    if (!amount) {
        return '';
    }

    const trim = (value) => String(Number(value.toFixed(2)));
    const rent = /month/i.test(price) ? '/mo' : '';

    if (amount >= 10_000_000) {
        return `${trim(amount / 10_000_000)} Cr${rent}`;
    }

    if (amount >= 100_000) {
        return `${trim(amount / 100_000)} L${rent}`;
    }

    return amount >= 1000 ? `${trim(amount / 1000)}K${rent}` : `${amount}${rent}`;
};

const HOUSE = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 3.2 2.8 11a1 1 0 0 0 1.3 1.5l.9-.8V20a1 1 0 0 0 1 1h4v-5.5h4V21h4a1 1 0 0 0 1-1v-8.3l.9.8a1 1 0 0 0 1.3-1.5Z"/></svg>';

const homeIcon = (L) => L.divIcon({
    className: 'uh-pin is-solo',
    html: `<span class="uh-pin-tag"><span class="uh-pin-mark">${HOUSE}</span></span>`,
    iconSize: [0, 0],
    iconAnchor: [0, 0],
});

const pinFor = (L, point, stacked) => {
    const label = pinLabel(point.price);
    const purpose = /month/i.test(String(point.price ?? '')) ? 'is-rent' : 'is-sale';

    return L.divIcon({
        className: `uh-pin ${purpose}${label ? '' : ' is-solo'}`,
        html: `<span class="uh-pin-tag"><span class="uh-pin-mark">${HOUSE}</span>${label ? `<span class="uh-pin-price">${escapeHtml(label)}</span>` : ''}</span>`,
        iconSize: [0, 0],
        iconAnchor: [0, stacked * 38],
    });
};

/**
 * Result cards carry data-spotlight="{id}" so a card and its pin can light each other up.
 */
const pinsById = new Map();

const setCardSpotlight = (id, on) => {
    document.querySelectorAll(`[data-spotlight="${Number(id)}"]`).forEach((card) => card.classList.toggle('is-spotlit', on));
};

const setPinSpotlight = (id, on) => {
    (pinsById.get(Number(id)) || []).forEach((marker) => {
        marker.getElement()?.classList.toggle('is-spotlit', on);
        marker.setZIndexOffset(on ? 1000 : 0);
    });
};

let selectedId = null;

const setSelected = (id) => {
    if (selectedId !== null) {
        (pinsById.get(selectedId) || []).forEach((marker) => marker.getElement()?.classList.remove('is-selected'));
        document.querySelectorAll(`[data-spotlight="${selectedId}"]`).forEach((card) => card.classList.remove('is-selected'));
    }

    selectedId = id === null ? null : Number(id);
    if (selectedId === null) {
        return;
    }

    (pinsById.get(selectedId) || []).forEach((marker) => marker.getElement()?.classList.add('is-selected'));
    const card = document.querySelector(`.uh-listing[data-spotlight="${selectedId}"]`);
    if (card && card.offsetParent !== null) {
        card.classList.add('is-selected');
        card.scrollIntoView({ block: 'nearest', behavior: prefersReducedMotion() ? 'auto' : 'smooth' });
    }
};

let spotlightBound = false;

const bindSpotlight = () => {
    if (spotlightBound) {
        return;
    }
    spotlightBound = true;

    const handler = (on) => (event) => {
        const card = event.target.closest?.('[data-spotlight]');
        if (!card || (event.relatedTarget instanceof Node && card.contains(event.relatedTarget))) {
            return;
        }
        setPinSpotlight(card.dataset.spotlight, on);
    };

    document.addEventListener('mouseover', handler(true));
    document.addEventListener('mouseout', handler(false));
    document.addEventListener('focusin', handler(true));
    document.addEventListener('focusout', handler(false));
};

const placeListing = (L, layer, point, seen) => {
    const key = `${Number(point.lat).toFixed(4)},${Number(point.lng).toFixed(4)}`;
    const stacked = seen.get(key) ?? 0;
    seen.set(key, stacked + 1);

    const marker = L.marker([point.lat, point.lng], { title: point.title, icon: pinFor(L, point, stacked), riseOnHover: true })
        .bindPopup(() => identityFor(point), { className: 'uh-pin-popup', minWidth: 232, maxWidth: 232, offset: [0, -46 - stacked * 38], autoPanPadding: [24, 24] })
        .addTo(layer);

    if (point.id) {
        pinsById.set(Number(point.id), [...(pinsById.get(Number(point.id)) || []), marker]);
        marker.on('mouseover', () => setCardSpotlight(point.id, true));
        marker.on('mouseout', () => setCardSpotlight(point.id, false));
        marker.on('popupopen', () => setSelected(point.id));
        marker.on('popupclose', () => {
            if (selectedId === Number(point.id)) {
                setSelected(null);
            }
        });
    }
};

const plot = (L, map, element, payload) => {
    const layer = L.featureGroup().addTo(map);
    const seen = new Map();

    (payload.points || []).forEach((point) => placeListing(L, layer, point, seen));

    (payload.clusters || []).forEach((cluster) => {
        if (cluster.count === 1 && cluster.url) {
            placeListing(L, layer, cluster, seen);
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
    const dedicated = element.dataset.scrollZoom === 'true';
    const map = L.map(element, { scrollWheelZoom: dedicated, zoomControl: true }).setView([lat, lng], Number(element.dataset.zoom || 12));

    let tileErrors = 0;
    L.tileLayer(element.dataset.tiles, { attribution: element.dataset.attribution, maxZoom: 19 })
        .on('tileerror', () => {
            tileErrors += 1;
            if (tileErrors === 4) {
                showMessage(element, 'Map tiles are not loading right now. Pins may still be shown.');
            }
        })
        .addTo(map);

    if (!dedicated) {
        map.on('focus', () => map.scrollWheelZoom.enable());
        map.on('blur', () => map.scrollWheelZoom.disable());
    }

    if (element.dataset.radius) {
        L.circle([lat, lng], { radius: Number(element.dataset.radius), color: MARK, weight: 1.5, opacity: 0.6, fillColor: MARK, fillOpacity: 0.08 }).addTo(map);
    }

    if (element.dataset.approximate === 'true') {
        L.circle([lat, lng], { radius: 600, color: MARK, weight: 1.5, opacity: 0.7, dashArray: '6 5', fillColor: MARK, fillOpacity: 0.1 }).addTo(map);
    } else if (element.dataset.pin === 'true') {
        L.marker([lat, lng], { icon: homeIcon(L), keyboard: false }).addTo(map);
    }

    if (element.dataset.src) {
        bindSpotlight();
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
