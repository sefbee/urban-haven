import $ from 'jquery';
import './admin-selects';

/**
 * Public search fields use Select2 with the site's own theme. Mark a select with data-uh-select;
 * data-uh-select-ajax adds live location suggestions, data-uh-select-tags lets a typed phrase
 * through as a keyword (value "q:<phrase>"), and data-uh-select-search="off" hides the search box.
 * Select2 only fires jQuery events, so a native change is re-dispatched for Alpine and plain listeners.
 */
const AUTO_SEARCH_THRESHOLD = 7;

const textRow = (primary, secondary, keyword = false) => {
    const row = $('<span class="uh-s2-option"></span>').toggleClass('is-keyword', keyword);
    row.append($('<span class="uh-s2-name"></span>').text(primary));
    if (secondary) {
        row.append($('<span class="uh-s2-meta"></span>').text(secondary));
    }

    return row;
};

const optionsFor = (el) => {
    const blank = el.querySelector('option[value=""]');
    const placeholder = el.dataset.placeholder || blank?.textContent.trim() || undefined;
    const optionCount = el.querySelectorAll('option:not([value=""])').length;
    const searchable = el.dataset.uhSelectSearch === 'on'
        || (el.dataset.uhSelectSearch !== 'off' && (el.dataset.uhSelectAjax || optionCount >= AUTO_SEARCH_THRESHOLD));
    const keywordLabel = el.dataset.uhSelectKeyword || window.uhCopyText?.('select_keyword') || '';

    const options = {
        theme: 'uh',
        width: '100%',
        placeholder,
        allowClear: el.dataset.uhSelectClear === 'true',
        minimumResultsForSearch: searchable ? 0 : Infinity,
        dropdownParent: $(document.body),
        dropdownAutoWidth: el.dataset.uhSelectAutoWidth === 'true',
        language: {
            noResults: () => el.dataset.uhSelectEmpty || window.uhCopyText?.('select_empty') || '',
            searching: () => el.dataset.uhSelectSearching || window.uhCopyText?.('select_searching') || '',
            errorLoading: () => el.dataset.uhSelectError || window.uhCopyText?.('select_error') || '',
        },
        templateResult: (item) => {
            if (item.loading || item.id === undefined) {
                return item.text;
            }
            if (item.keyword) {
                return textRow(keywordLabel.replace(':term', item.text), null, true);
            }

            return item.meta || item.city ? textRow(item.text, [item.city, item.meta].filter(Boolean).join(' · ')) : item.text;
        },
        templateSelection: (item) => item.text,
    };

    if (el.dataset.uhSelectAjax) {
        options.ajax = {
            url: el.dataset.uhSelectAjax,
            dataType: 'json',
            delay: 200,
            cache: true,
            data: (params) => ({ q: params.term || '' }),
            processResults: (payload) => ({ results: payload.results || [] }),
        };
    }

    if (el.dataset.uhSelectTags !== undefined) {
        options.tags = true;
        options.createTag = (params) => {
            const term = String(params.term || '').trim();

            return term === '' ? null : { id: `q:${term}`, text: term, keyword: true };
        };
        options.insertTag = (data, tag) => data.push(tag);
    }

    return options;
};

const enhance = (el) => {
    if (el.dataset.uhSelect2 === '1' || !$.fn.select2) {
        return;
    }

    const $el = $(el);
    $el.select2(optionsFor(el));
    el.dataset.uhSelect2 = '1';

    $el.on('change', (event) => {
        if (!event.originalEvent) {
            el.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });

    $el.on('select2:open', () => {
        document.querySelector('.select2-container--open .select2-search__field')?.focus();
    });
};

export const bootPublicSelects = (root = document) => {
    if (!document.body.classList.contains('uh-home')) {
        return;
    }

    root.querySelectorAll('select[data-uh-select]').forEach(enhance);
};

/**
 * Alpine writes values straight to the element; ask Select2 to redraw what it shows.
 */
window.addEventListener('uh:selects-sync', () => {
    document.querySelectorAll('select[data-uh-select2="1"][data-uh-select]').forEach((el) => {
        $(el).trigger('change.select2');
    });
});
