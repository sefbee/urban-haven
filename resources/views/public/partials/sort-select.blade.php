<div class="uh-sort">
    <label class="sr-only" for="sort-results">{{ __('Sort by') }}</label>
    <select id="sort-results" name="sort" form="property-filters" x-on:change="$el.form.requestSubmit()"
            data-uh-select data-uh-select-search="off" data-uh-select-auto-width="true">
        @foreach($sortOptions as $value => $label)
            <option value="{{ $value }}" @selected(($filters['sort'] ?? 'newest') === $value)>{{ $label }}</option>
        @endforeach
    </select>
</div>
