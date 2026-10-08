/**
 * Admin form helpers: the shared SEO panel and the country → city → area picker.
 */

export const slugify = (value) => String(value ?? '')
    .normalize('NFKD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .replace(/&/g, ' and ')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')
    .slice(0, 120)
    .replace(/-+$/g, '');

const plain = (value) => String(value ?? '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();

const contains = (haystack, needle) => needle !== '' && plain(haystack).toLowerCase().includes(needle.toLowerCase());

const refreshSelect2 = (root) => {
    if (! window.jQuery?.fn?.select2) {
        return;
    }

    root.querySelectorAll('select[data-uh-select2="1"]').forEach((select) => {
        window.jQuery(select).trigger('change.select2');
    });
};

export const registerAdminForms = (Alpine) => {
    Alpine.data('uhSeo', (config = {}) => ({
        keyword: config.keyword ?? '',
        title: config.title ?? '',
        description: config.description ?? '',
        slug: config.slug ?? '',
        autoSlug: Boolean(config.autoSlug),
        sourceTitle: '',
        sourceContent: '',
        copied: false,
        siteName: config.siteName ?? '',
        init() {
            const form = this.$root.closest('form');
            const titleInput = form?.querySelector(`[name="${config.titleSource}"]`);
            const contentInput = config.contentSource ? form?.querySelector(`[name="${config.contentSource}"]`) : null;

            const readTitle = () => {
                this.sourceTitle = titleInput?.value ?? '';
                if (this.autoSlug) {
                    this.slug = slugify(this.sourceTitle);
                }
            };
            const readContent = () => {
                this.sourceContent = contentInput?.value ?? '';
            };

            titleInput?.addEventListener('input', readTitle);
            contentInput?.addEventListener('input', readContent);
            readContent();
            this.sourceTitle = titleInput?.value ?? '';
            if (this.autoSlug && this.sourceTitle) {
                this.slug = slugify(this.sourceTitle);
            }
        },
        slugify,
        slugEdited() {
            this.autoSlug = this.slug === '';
        },
        regenerate() {
            this.slug = slugify(this.sourceTitle || config.fallbackTitle || '');
            this.autoSlug = true;
        },
        copy() {
            navigator.clipboard?.writeText(this.previewUrl).then(() => {
                this.copied = true;
                setTimeout(() => { this.copied = false; }, 1500);
            });
        },
        get autoTitle() {
            return plain(config.fallbackTitle || this.sourceTitle || '');
        },
        get autoDescription() {
            const text = plain(this.sourceContent || config.fallbackDescription || '');
            return text.length > 160 ? `${text.slice(0, 157).trimEnd()}…` : text;
        },
        get previewTitle() {
            const text = this.title || this.autoTitle || 'Page title';
            return text.length > 62 ? `${text.slice(0, 60).trimEnd()}…` : text;
        },
        get previewDescription() {
            return this.description || this.autoDescription || 'Add a description so Google shows your own words instead of picking text from the page.';
        },
        get previewUrl() {
            if (config.staticPath !== null && config.staticPath !== undefined) {
                return `${config.baseUrl.replace(/\/$/, '')}${config.staticPath}`;
            }

            return `${config.baseUrl}${this.slug || slugify(this.sourceTitle) || ''}`;
        },
        get titleTone() {
            const length = (this.title || this.autoTitle).length;
            return length === 0 ? 'is-empty' : (length >= 30 && length <= 60 ? 'is-good' : (length > 60 ? 'is-bad' : 'is-warn'));
        },
        get descriptionTone() {
            const length = (this.description || this.autoDescription).length;
            return length === 0 ? 'is-empty' : (length >= 120 && length <= 160 ? 'is-good' : (length > 160 ? 'is-bad' : 'is-warn'));
        },
        get checks() {
            const keyword = this.keyword.trim();
            const title = this.title || this.autoTitle;
            const description = this.description || this.autoDescription;
            const slugKeyword = slugify(keyword);
            const checks = [
                { label: 'Focus keyword is set', ok: keyword !== '' },
                { label: 'Keyword appears in the meta title', ok: contains(title, keyword) },
                { label: 'Keyword appears in the meta description', ok: contains(description, keyword) },
                { label: 'Keyword appears in the URL', ok: slugKeyword !== '' && this.previewUrl.includes(slugKeyword) },
                { label: 'Meta title is 30–60 characters', ok: title.length >= 30 && title.length <= 60 },
                { label: 'Meta description is 120–160 characters', ok: description.length >= 120 && description.length <= 160 },
            ];

            if (config.contentSource) {
                checks.push({ label: 'Keyword appears in the page content', ok: contains(this.sourceContent, keyword) });
            }

            return checks;
        },
        get passed() {
            return this.checks.filter((check) => check.ok).length;
        },
        get grade() {
            const ratio = this.passed / this.checks.length;
            return ratio >= 0.8 ? 'good' : (ratio >= 0.5 ? 'warn' : 'bad');
        },
        get gradeLabel() {
            return { good: 'Good', warn: 'Needs work', bad: 'Poor' }[this.grade];
        },
    }));

    /**
     * Country → city → area. Only the area (and, where the record stores it, the city) is submitted;
     * the country and city lists narrow the choices in the order people think about a location.
     */
    Alpine.data('uhPlacePicker', ({ areas = [], areaId = '', city = '', country = '', defaultCountry = 'Bangladesh' } = {}) => ({
        areas: areas.map((area) => ({ ...area, id: String(area.id) })),
        areaId: String(areaId ?? ''),
        country: '',
        city: '',
        init() {
            const selected = this.areas.find((area) => area.id === this.areaId);
            this.country = selected?.country || country || (this.countries.includes(defaultCountry) ? defaultCountry : (this.countries[0] ?? ''));
            this.city = selected?.city || city || (this.cities.length === 1 ? this.cities[0] : '');

            this.$watch('country', () => {
                if (! this.cities.includes(this.city)) {
                    this.city = this.cities.length === 1 ? this.cities[0] : '';
                }
                this.syncArea();
            });
            this.$watch('city', () => this.syncArea());

            window.addEventListener('uh-place-added', (event) => {
                const area = event.detail;
                if (! area?.id) {
                    return;
                }
                if (! this.areas.some((item) => item.id === String(area.id))) {
                    this.areas.push({ id: String(area.id), name: area.name, city: area.city, country: area.country || defaultCountry });
                }
                if (area.target !== this.$root.querySelector('[data-place-area]')?.id) {
                    return;
                }
                this.country = area.country || defaultCountry;
                this.city = area.city;
                this.areaId = String(area.id);
                this.refresh();
            });

            this.refresh();
        },
        get countries() {
            return [...new Set([defaultCountry, ...this.areas.map((area) => area.country || defaultCountry)])].sort();
        },
        get cities() {
            const list = new Set(this.areas.filter((area) => (area.country || defaultCountry) === this.country).map((area) => area.city));
            if (city && this.country === (country || defaultCountry)) {
                list.add(city);
            }

            return [...list].filter(Boolean).sort();
        },
        get choices() {
            return this.areas
                .filter((area) => (area.country || defaultCountry) === this.country && (this.city === '' || area.city === this.city))
                .sort((a, b) => a.name.localeCompare(b.name));
        },
        syncArea() {
            if (! this.choices.some((area) => area.id === this.areaId)) {
                this.areaId = '';
            }
            this.refresh();
        },
        refresh() {
            this.$nextTick(() => refreshSelect2(this.$root));
        },
    }));

    /**
     * Fills a key/slug field from a label until someone edits it by hand.
     */
    Alpine.data('uhAutoSlug', ({ value = '', separator = '-' } = {}) => ({
        slug: value,
        touched: value !== '',
        fill(label) {
            if (! this.touched) {
                this.slug = slugify(label).replace(/-/g, separator);
            }
        },
        edited() {
            this.touched = this.slug !== '';
            this.slug = slugify(this.slug).replace(/-/g, separator);
        },
    }));

    /**
     * Password field with a strong-password generator; a generated value is shown and copied.
     */
    Alpine.data('uhPassword', () => ({
        shown: false,
        copied: false,
        async generate() {
            const alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%*?';
            const bytes = crypto.getRandomValues(new Uint32Array(16));
            const input = this.$refs.password;
            input.value = [...bytes].map((byte) => alphabet[byte % alphabet.length]).join('');
            input.dispatchEvent(new Event('input', { bubbles: true }));
            this.shown = true;
            try {
                await navigator.clipboard.writeText(input.value);
                this.copied = true;
            } catch {
                this.copied = false;
            }
        },
    }));

    /**
     * Rich text without a third-party editor. The toolbar only produces the tags the public
     * pages allow (config/purify.php); pasted content is reduced to the same set.
     */
    Alpine.data('uhRichText', () => ({
        focused: false,
        active: [],
        words: 0,
        init() {
            const editor = this.$refs.editor;
            editor.innerHTML = toEditorHtml(this.$refs.input.value);
            this.count();
            this.onSelection = () => {
                if (document.activeElement === editor) {
                    this.refreshActive();
                }
            };
            document.addEventListener('selectionchange', this.onSelection);
        },
        destroy() {
            document.removeEventListener('selectionchange', this.onSelection);
        },
        focus() {
            this.$refs.editor.focus();
        },
        run(command) {
            const editor = this.$refs.editor;
            editor.focus();
            document.execCommand('defaultParagraphSeparator', false, 'p');

            if (['h2', 'h3', 'p', 'blockquote'].includes(command)) {
                const current = document.queryCommandValue('formatBlock').toLowerCase();
                document.execCommand('formatBlock', false, current === command && command !== 'p' ? '<p>' : `<${command}>`);
            } else if (command === 'link') {
                const href = window.prompt('Link address (https://…, /page, mailto: or tel:)', 'https://');
                if (href === null) {
                    return;
                }
                const clean = href.trim();
                if (clean === '' || clean === 'https://') {
                    document.execCommand('unlink');
                } else if (/^(https?:\/\/|\/|mailto:|tel:)/i.test(clean)) {
                    if (window.getSelection()?.isCollapsed) {
                        document.execCommand('insertHTML', false, `<a href="${escapeAttribute(clean)}">${escapeHtml(clean)}</a>`);
                    } else {
                        document.execCommand('createLink', false, clean);
                    }
                }
            } else if (command === 'clear') {
                document.execCommand('removeFormat');
                document.execCommand('unlink');
                document.execCommand('formatBlock', false, '<p>');
            } else {
                document.execCommand(command);
            }

            this.sync();
            this.refreshActive();
        },
        shortcut(event) {
            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                this.run('link');
            }
        },
        paste(event) {
            const data = event.clipboardData;
            if (! data) {
                return;
            }
            event.preventDefault();
            const html = data.getData('text/html');
            document.execCommand('insertHTML', false, html ? cleanHtml(html) : textToHtml(data.getData('text/plain')));
            this.sync();
        },
        sync() {
            const html = cleanHtml(this.$refs.editor.innerHTML);
            const value = this.$refs.editor.textContent.trim() === '' ? '' : html;
            if (this.$refs.input.value !== value) {
                this.$refs.input.value = value;
                this.$refs.input.dispatchEvent(new Event('input', { bubbles: true }));
            }
            this.count();
        },
        count() {
            const text = this.$refs.editor.textContent.trim();
            this.words = text === '' ? 0 : text.split(/\s+/).length;
        },
        refreshActive() {
            const block = document.queryCommandValue('formatBlock').toLowerCase();
            this.active = [
                ...['bold', 'italic', 'underline', 'insertUnorderedList', 'insertOrderedList'].filter((command) => document.queryCommandState(command)),
                ...(['h2', 'h3', 'blockquote'].includes(block) ? [block] : []),
            ];
        },
    }));
};

const RICH_TAGS = {
    P: 'p', H2: 'h2', H3: 'h3', H4: 'h4', H1: 'h2', STRONG: 'strong', B: 'strong', EM: 'em', I: 'em', U: 'u',
    S: 's', DEL: 's', STRIKE: 's', A: 'a', UL: 'ul', OL: 'ol', LI: 'li', BLOCKQUOTE: 'blockquote', BR: 'br',
};
const DROP_WITH_CONTENT = new Set(['SCRIPT', 'STYLE', 'IFRAME', 'OBJECT', 'EMBED', 'TEMPLATE', 'META', 'LINK', 'TITLE', 'svg', 'SVG']);

const escapeHtml = (value) => String(value).replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char]));
const escapeAttribute = escapeHtml;

const textToHtml = (text) => String(text ?? '')
    .replace(/\r\n?/g, '\n')
    .split(/\n{2,}/)
    .map((block) => block.trim())
    .filter(Boolean)
    .map((block) => `<p>${escapeHtml(block).replace(/\n/g, '<br>')}</p>`)
    .join('');

const toEditorHtml = (value) => {
    const source = String(value ?? '').trim();
    if (source === '') {
        return '<p><br></p>';
    }

    return /<\/?[a-z][\s\S]*>/i.test(source) ? cleanHtml(source) : textToHtml(source);
};

const cleanNode = (node, documentRef) => {
    const fragment = documentRef.createDocumentFragment();
    node.childNodes.forEach((child) => {
        if (child.nodeType === Node.TEXT_NODE) {
            fragment.appendChild(documentRef.createTextNode(child.textContent));
            return;
        }
        if (child.nodeType !== Node.ELEMENT_NODE || DROP_WITH_CONTENT.has(child.nodeName)) {
            return;
        }

        const style = child.getAttribute('style') ?? '';
        let tag = RICH_TAGS[child.nodeName];
        if (! tag && child.nodeName === 'SPAN') {
            tag = /font-weight:\s*(bold|[6-9]00)/i.test(style) ? 'strong' : (/font-style:\s*italic/i.test(style) ? 'em' : null);
        }
        if (! tag && child.nodeName === 'DIV') {
            tag = 'p';
        }

        const inner = cleanNode(child, documentRef);
        if (! tag) {
            fragment.appendChild(inner);
            return;
        }

        const element = documentRef.createElement(tag);
        if (tag === 'a') {
            const href = child.getAttribute('href') ?? '';
            if (! /^(https?:\/\/|\/|mailto:|tel:|#)/i.test(href)) {
                fragment.appendChild(inner);
                return;
            }
            element.setAttribute('href', href);
        }
        element.appendChild(inner);
        fragment.appendChild(element);
    });

    return fragment;
};

const cleanHtml = (html) => {
    const parsed = new DOMParser().parseFromString(`<body>${html}</body>`, 'text/html');
    const container = document.createElement('div');
    container.appendChild(cleanNode(parsed.body, document));
    container.querySelectorAll('p, h2, h3, h4, li, blockquote').forEach((element) => {
        if (element.textContent.trim() === '' && ! element.querySelector('br')) {
            element.remove();
        }
    });
    container.querySelectorAll('p p, li p').forEach((element) => element.replaceWith(...element.childNodes));

    return container.innerHTML.replace(/<p><br><\/p>$/i, '').trim();
};
