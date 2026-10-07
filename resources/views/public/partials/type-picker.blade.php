{{-- Main type toggle above a checklist of its sub-types. State lives in the surrounding typePicker() Alpine data. --}}
<div class="uh-type-picker">
    <p class="uh-type-title">{{ __('Property type') }}</p>
    <div class="uh-type-main" role="group" aria-label="{{ __('Main type') }}">
        @foreach(\App\Models\PropertyType::categoryLabels() as $categoryKey => $categoryLabel)
            <button type="button" class="uh-type-main-btn" :aria-pressed="(category === @js($categoryKey)).toString()"
                    @click="setCategory(@js($categoryKey))">{{ $categoryLabel }}</button>
        @endforeach
    </div>
    <div class="uh-type-grid" role="group" aria-label="{{ __('Sub-types') }}">
        <template x-for="option in visibleTypes()" :key="'sub' + option.id">
            <label class="uh-type-check">
                <input type="checkbox" :value="option.id" x-model="pickedTypes">
                <span x-text="option.label"></span>
            </label>
        </template>
    </div>
</div>
