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

/**
 * When the matching result card is on the page, the popup borrows its photograph and place so the
 * pin opens as the same property the visitor has been looking at in the list.
 */
const identityFor = (point) => {
    const card = document.querySelector(`.uh-listing[data-spotlight="${Number(point.id)}"]`);
    const image = card?.querySelector('.uh-listing-frame img');
    const source = image ? safeUrl(image.currentSrc || image.src) : '#';
    const place = card?.querySelector('.uh-listing-place > span:first-child')?.textContent.trim();

    if (!card) {
        return popupFor(point);
    }

    return `<a class="uh-pin-card" href="${escapeHtml(safeUrl(point.url))}">${
        source !== '#' ? `<img src="${escapeHtml(source)}" alt="">` : ''
    }<span class="uh-pin-card-body">${
        place ? `<span class="uh-pin-card-place">${escapeHtml(place)}</span>` : ''
    }<strong class="uh-pin-card-title">${escapeHtml(point.title)}</strong>${
        point.price ? `<span class="uh-pin-card-price">${escapeHtml(point.price)}</span>` : ''
    }${point.approximate ? '<em>Approximate location</em>' : ''}<span class="uh-pin-card-cta">Explore this property &rarr;</span></span></a>`;
};

const MARK = '#1d1d1f';

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

const pinFor = (L, point, stacked) => {
    const label = pinLabel(point.price);

    if (!label) {
        return L.divIcon({ className: 'uh-pin-dot', html: '<span></span>', iconSize: [16, 16] });
    }

    return L.divIcon({
        className: 'uh-pin',
        html: `<span>${escapeHtml(label)}</span>`,
        iconSize: [0, 0],
        iconAnchor: [0, 8 + stacked * 26],
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
        .bindPopup(() => identityFor(point), { className: 'uh-pin-popup', minWidth: 248, maxWidth: 248, offset: [0, -34 - stacked * 26] })
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
        L.circle([lat, lng], { radius: Number(element.dataset.radius), color: MARK, weight: 1, opacity: 0.35, fillColor: MARK, fillOpacity: 0.05 }).addTo(map);
    }

    if (element.dataset.approximate === 'true') {
        L.circle([lat, lng], { radius: 600, color: MARK, weight: 1, opacity: 0.35, dashArray: '4 4', fillColor: MARK, fillOpacity: 0.04 }).addTo(map);
    } else if (element.dataset.pin === 'true') {
        L.marker([lat, lng], { icon: L.divIcon({ className: 'uh-pin-dot', html: '<span></span>', iconSize: [16, 16] }) }).addTo(map);
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
