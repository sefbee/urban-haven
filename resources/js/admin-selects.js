import $ from 'jquery';
import select2 from 'select2';
import 'select2/dist/css/select2.css';

window.jQuery = window.$ = $;

if (typeof $.isArray !== 'function') {
    $.isArray = Array.isArray;
}

if (typeof $.isFunction !== 'function') {
    $.isFunction = (value) => typeof value === 'function';
}

if (typeof $.trim !== 'function') {
    $.trim = (text) => (text == null ? '' : String(text).trim());
}

if (typeof select2 === 'function' && ! $.fn.select2) {
    select2($);
}

const enhance = (el) => {
    if (! el || el.dataset.uhSelect2 === '1' || ! window.jQuery?.fn?.select2) {
        return;
    }

    const $el = $(el);
    const blank = el.querySelector('option[value=""]');
    const sheet = el.closest('.uh-admin-sheet');

    $el.select2({
        width: '100%',
        minimumResultsForSearch: 0,
        dropdownAutoWidth: false,
        placeholder: blank ? blank.textContent.trim() : undefined,
        allowClear: Boolean(blank),
        dropdownParent: sheet ? $(sheet) : $(document.body),
    });

    el.dataset.uhSelect2 = '1';

    $el.on('change.select2', () => {
        el.dispatchEvent(new Event('input', { bubbles: true }));
    });

    // jQuery's change event never reaches native listeners such as Alpine's x-model on selects.
    $el.on('select2:select select2:clear', () => {
        el.dispatchEvent(new Event('change', { bubbles: true }));
    });
};

export const bootAdminSelects = (root = document) => {
    if (! document.body.classList.contains('uh-admin')) {
        return;
    }

    root.querySelectorAll('select.uh-select:not([data-native-select])').forEach((el) => enhance(el));
};

export const applySelectOption = (target, payload) => {
    if (! payload?.id) {
        return;
    }

    const id = String(payload.id);
    const label = payload.label ?? id;

    if (target === 'amenity-list') {
        const list = document.getElementById('amenity-list');
        if (! list || list.querySelector(`input[name="amenity_ids[]"][value="${id}"]`)) {
            return;
        }

        const item = document.createElement('label');
        item.className = 'uh-check';
        item.innerHTML = `<input type="checkbox" name="amenity_ids[]" value="${id}" checked> <span></span>`;
        item.querySelector('span').textContent = label;
        list.append(item);

        return;
    }

    const select = document.getElementById(target);
    if (! select) {
        return;
    }

    if (select.hasAttribute('data-place-area')) {
        window.dispatchEvent(new CustomEvent('uh-place-added', { detail: { ...payload, target } }));

        return;
    }

    if (! select.querySelector(`option[value="${CSS.escape(id)}"]`)) {
        select.append(new Option(label, id, true, true));
    }

    select.value = id;

    if (payload.profile && window.Alpine) {
        const form = select.closest('form');
        if (form) {
            const scope = window.Alpine.$data(form);
            if (scope?.profiles) {
                scope.profiles[id] = payload.profile;
                scope.typeId = id;
            }
        }
    }

    if (window.jQuery?.fn?.select2 && select.dataset.uhSelect2 === '1') {
        window.jQuery(select).val(id).trigger('change');
    } else {
        select.dispatchEvent(new Event('input', { bubbles: true }));
        select.dispatchEvent(new Event('change', { bubbles: true }));
    }
};

window.uhBootAdminSelects = bootAdminSelects;
window.uhApplySelectOption = applySelectOption;
