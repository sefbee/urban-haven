/**
 * Consent-aware analytics. Events always go to the local dataLayer; GTM, GA4 and the
 * Meta Pixel are only injected after the visitor grants consent.
 */
const PIXEL_EVENTS = {
    inquiry_success: 'Lead',
    visit_request_success: 'Schedule',
    property_view: 'ViewContent',
    search: 'Search',
    phone_click: 'Contact',
    whatsapp_click: 'Contact',
};

const BEACON_EVENTS = ['phone_click', 'whatsapp_click', 'property_cta_click'];

let loaded = false;

const inject = (src, attributes = {}) => {
    const script = document.createElement('script');
    script.async = true;
    script.src = src;
    Object.entries(attributes).forEach(([key, value]) => script.setAttribute(key, value));
    document.head.appendChild(script);
};

export const bootAnalytics = (consent) => {
    const ids = window.uhAnalytics?.ids || {};

    if (typeof window.gtag === 'function' && (consent === 'granted' || consent === 'denied')) {
        const state = consent === 'granted' ? 'granted' : 'denied';
        window.gtag('consent', 'update', { analytics_storage: state, ad_storage: state, ad_user_data: state, ad_personalization: state });
    }

    if (consent !== 'granted' || loaded) {
        return;
    }

    loaded = true;

    if (ids.gtm) {
        window.dataLayer.push({ 'gtm.start': Date.now(), event: 'gtm.js' });
        inject(`https://www.googletagmanager.com/gtm.js?id=${encodeURIComponent(ids.gtm)}`);
    } else if (ids.ga4) {
        inject(`https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(ids.ga4)}`);
        window.gtag('js', new Date());
        window.gtag('config', ids.ga4, { anonymize_ip: true });
    }

    if (ids.ads && !ids.gtm) {
        if (!ids.ga4) {
            inject(`https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(ids.ads)}`);
            window.gtag('js', new Date());
        }
        window.gtag('config', ids.ads);
    }

    if (ids.pixel && !window.fbq) {
        const fbq = function (...args) {
            fbq.callMethod ? fbq.callMethod(...args) : fbq.queue.push(args);
        };
        fbq.queue = [];
        fbq.loaded = true;
        fbq.version = '2.0';
        window.fbq = fbq;
        window._fbq = fbq;
        inject('https://connect.facebook.net/en_US/fbevents.js');
        window.fbq('init', ids.pixel);
        window.fbq('track', 'PageView');
    }
};

export const track = (event, params = {}) => {
    if (!event) {
        return;
    }

    const { event: _ignored, ...rest } = params || {};
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({ event, ...rest });

    if (loaded && !window.uhAnalytics?.ids?.gtm && typeof window.gtag === 'function') {
        window.gtag('event', event, rest);
    }

    if (loaded && typeof window.fbq === 'function' && PIXEL_EVENTS[event]) {
        window.fbq('track', PIXEL_EVENTS[event], { content_ids: rest.property_id ? [String(rest.property_id)] : undefined });
    }

    if (BEACON_EVENTS.includes(event) && window.uhAnalytics?.trackUrl) {
        const body = new FormData();
        body.append('event', event);
        if (rest.property_id) {
            body.append('property_id', rest.property_id);
        }
        body.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
        navigator.sendBeacon?.(window.uhAnalytics.trackUrl, body);
    }
};

/**
 * Any element with data-track="event_name" reports a click; data-track-* attributes become parameters.
 */
export const bindTrackedClicks = () => {
    document.addEventListener('click', (event) => {
        const element = event.target.closest?.('[data-track]');
        if (!element) {
            return;
        }

        const params = {};
        Object.entries(element.dataset).forEach(([key, value]) => {
            if (key.startsWith('track') && key !== 'track') {
                const name = key.slice(5).replace(/^[A-Z]/, (char) => char.toLowerCase()).replace(/[A-Z]/g, (char) => `_${char.toLowerCase()}`);
                params[name] = value;
            }
        });

        track(element.dataset.track, params);
    });
};
