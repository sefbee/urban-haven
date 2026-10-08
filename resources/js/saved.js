/**
 * Shortlist, compare and recently viewed, kept in versioned localStorage so visitors need no account.
 * The server only ever receives IDs and returns cards for listings that are still published.
 */
const KEYS = {
    shortlist: 'uh:v1:shortlist',
    compare: 'uh:v1:compare',
    recent: 'uh:v1:recent',
};

const LIMITS = { shortlist: 24, compare: 4, recent: 10 };
const copy = (key, replacements = {}) => window.uhCopyText?.(key, replacements) || '';

const read = (key) => {
    try {
        const value = JSON.parse(localStorage.getItem(key) || '[]');
        return Array.isArray(value) ? value.map(Number).filter((id) => Number.isInteger(id) && id > 0) : [];
    } catch {
        return [];
    }
};

const write = (key, ids) => {
    try {
        localStorage.setItem(key, JSON.stringify(ids));
        return true;
    } catch {
        return false;
    }
};

export const registerSavedStore = (Alpine) => {
    Alpine.store('saved', {
        shortlist: read(KEYS.shortlist),
        compare: read(KEYS.compare),
        recent: read(KEYS.recent),
        notice: '',
        storageAvailable: write('uh:v1:probe', []),

        has(list, id) {
            return this[list].includes(Number(id));
        },

        toggle(list, id) {
            id = Number(id);

            if (this.has(list, id)) {
                this[list] = this[list].filter((value) => value !== id);
                this.notice = list === 'compare' ? copy('saved_removed_compare') : copy('saved_removed_shortlist');
            } else if (this[list].length >= LIMITS[list]) {
                this.notice = list === 'compare'
                    ? copy('saved_compare_limit', { limit: LIMITS.compare })
                    : copy('saved_shortlist_limit', { limit: LIMITS.shortlist });
                return;
            } else {
                this[list] = [...this[list], id];
                if (list === 'compare') {
                    this.notice = this.compare.length === 1
                        ? copy('saved_compare_added_first')
                        : copy('saved_compare_added', { count: this.compare.length });
                } else {
                    this.notice = copy('saved_shortlist_added');
                }
                window.uhTrack?.(list === 'compare' ? 'compare_add' : 'shortlist_add', { property_id: id });
            }

            if (!write(KEYS[list], this[list])) {
                this.notice = copy('saved_storage_error');
            }
        },

        viewed(id) {
            id = Number(id);
            this.recent = [id, ...this.recent.filter((value) => value !== id)].slice(0, LIMITS.recent);
            write(KEYS.recent, this.recent);
        },

        /**
         * Replace a list with the IDs the server confirmed are still published.
         */
        prune(list, validIds) {
            const kept = this[list].filter((id) => validIds.includes(id));

            if (kept.length === this[list].length) {
                return;
            }

            this[list] = kept;
            write(KEYS[list], kept);
        },

        clear(list) {
            this[list] = [];
            write(KEYS[list], []);
        },
    });

    Alpine.data('uhSavedList', (list, endpoint, view = 'card', exclude = null) => ({
        loading: true,
        failed: false,
        html: '',
        count: 0,
        async init() {
            this.$watch(`$store.saved.${list}`, () => this.load());
            await this.load();
        },
        async load() {
            const ids = Alpine.store('saved')[list].filter((id) => id !== Number(exclude));
            if (ids.length === 0) {
                this.html = '';
                this.count = 0;
                this.loading = false;
                return;
            }

            this.loading = true;
            this.failed = false;

            try {
                const response = await fetch(`${endpoint}?view=${view}&ids=${ids.join(',')}`, { headers: { Accept: 'application/json' } });
                if (!response.ok) {
                    throw new Error(String(response.status));
                }
                const payload = await response.json();
                if (exclude === null) {
                    Alpine.store('saved').prune(list, payload.ids);
                }
                this.count = payload.ids.length;
                this.html = payload.html;
            } catch {
                this.failed = true;
            } finally {
                this.loading = false;
            }
        },
    }));
};
