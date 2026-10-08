@php
    $groupLabels = $groupLabels ?? \App\Support\SettingsSchema::GROUP_LABELS;
@endphp

@foreach($groups as $group)
    <input type="hidden" name="groups[]" value="{{ $group }}">
@endforeach

@foreach($definitions as $group => $items)
    <section class="uh-panel" id="setting-{{ $group }}">
        <h2 class="uh-h4">{{ $groupLabels[$group] ?? \Illuminate\Support\Str::headline($group) }}</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            @foreach($items as $key => $definition)
                @php
                    $fieldId = 'setting-'.$key;
                    $current = old('settings.'.$key, $values[$key] ?? ($definition['default'] ?? null));
                    $error = $errors->first('settings.'.$key) ?: $errors->first('settings.'.$key.'.*') ?: $errors->first('images.'.$key);
                @endphp
                @if($definition['input'] === 'checkbox')
                    <div class="uh-field sm:col-span-2">
                        <input type="hidden" name="settings[{{ $key }}]" value="0">
                        <label class="dd-switch">
                            <input type="checkbox" id="{{ $fieldId }}" name="settings[{{ $key }}]" value="1" @checked(filter_var($current, FILTER_VALIDATE_BOOLEAN))>
                            <span class="dd-switch-track" aria-hidden="true"></span>
                            <span class="dd-switch-label">{{ $definition['label'] }}</span>
                        </label>
                        @if(! empty($definition['help']))<p class="uh-hint ml-12">{{ $definition['help'] }}</p>@endif
                        @if($error)<p class="uh-error">{{ $error }}</p>@endif
                    </div>
                @elseif($definition['input'] === 'image')
                    @php $path = (string) ($values[$key] ?? ''); @endphp
                    <div class="uh-field" x-data="{ preview: null, remove: false }">
                        <span class="uh-label" id="{{ $fieldId }}-label">{{ $definition['label'] }}</span>
                        <div class="dd-image-field">
                            <div @class(['dd-image-preview', 'is-dark' => $key === 'brand_logo_dark'])>
                                <template x-if="preview"><img :src="preview" alt=""></template>
                                @if($path !== '')
                                    <img x-show="! preview && ! remove" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($path) }}" alt="Current {{ strtolower($definition['label']) }}">
                                @endif
                                <span class="dd-image-empty" x-show="! preview && {{ $path === '' ? 'true' : 'remove' }}"><x-icon name="image" class="size-6" /></span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <label class="uh-btn-outline uh-btn-sm cursor-pointer">
                                    <x-icon name="upload" class="size-4" />
                                    <span>{{ $path === '' ? 'Upload' : 'Replace' }}</span>
                                    <input type="file" id="{{ $fieldId }}" name="images[{{ $key }}]" class="sr-only" aria-labelledby="{{ $fieldId }}-label"
                                           accept="image/png,image/jpeg,image/webp{{ $key === 'brand_favicon' ? ',image/x-icon,image/vnd.microsoft.icon' : '' }}"
                                           @change="const file = $event.target.files[0]; preview = file ? URL.createObjectURL(file) : null; if (file) remove = false">
                                </label>
                                @if($path !== '')
                                    <label class="uh-check mt-2 text-xs">
                                        <input type="checkbox" name="remove_images[{{ $key }}]" value="1" x-model="remove">
                                        <span>Remove</span>
                                    </label>
                                @endif
                                @if(! empty($definition['help']))<p class="uh-hint">{{ $definition['help'] }}</p>@endif
                            </div>
                        </div>
                        @if($error)<p class="uh-error">{{ $error }}</p>@endif
                    </div>
                @elseif($definition['input'] === 'color')
                    <div class="uh-field" x-data="{ value: @js(is_string($current) ? $current : '#000000') }">
                        <label class="uh-label" for="{{ $fieldId }}">{{ $definition['label'] }}</label>
                        <div class="dd-color-field">
                            <input type="color" class="dd-color-swatch" :value="/^#[0-9a-f]{6}$/i.test(value) ? value : '#000000'" @input="value = $event.target.value" aria-label="Pick {{ strtolower($definition['label']) }}">
                            <input id="{{ $fieldId }}" class="uh-input font-mono uppercase" name="settings[{{ $key }}]" x-model="value" maxlength="7" pattern="#[0-9a-fA-F]{6}" dir="ltr" data-theme-key="{{ $key }}">
                        </div>
                        @if(! empty($definition['help']))<p class="uh-hint">{{ $definition['help'] }}</p>@endif
                        @if($error)<p class="uh-error">{{ $error }}</p>@endif
                    </div>
                @else
                    <div @class(['uh-field', 'sm:col-span-2' => in_array($definition['input'], ['textarea', 'lines'], true)])>
                        <label class="uh-label" for="{{ $fieldId }}">{{ $definition['label'] }}</label>
                        @if($definition['input'] === 'textarea')
                            <textarea id="{{ $fieldId }}" class="uh-textarea" rows="3" name="settings[{{ $key }}]" maxlength="500"
                                      @isset($definition['placeholder']) placeholder="{{ $definition['placeholder'] }}" @endisset>{{ $current ?: ($key === 'consent_text' ? \App\Support\SettingsSchema::defaultConsentText() : '') }}</textarea>
                        @elseif($definition['input'] === 'lines')
                            <textarea id="{{ $fieldId }}" class="uh-textarea font-mono text-xs" rows="4" name="settings[{{ $key }}]" dir="ltr">{{ is_array($current) ? implode("\n", $current) : $current }}</textarea>
                        @elseif($definition['input'] === 'select')
                            <select id="{{ $fieldId }}" class="uh-select" name="settings[{{ $key }}]">
                                @foreach($definition['options'] as $value => $label)
                                    <option value="{{ $value }}" @selected((string) $current === (string) $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        @else
                            <input id="{{ $fieldId }}" class="uh-input" name="settings[{{ $key }}]" value="{{ is_scalar($current) ? $current : '' }}"
                                   type="{{ $definition['input'] }}"
                                   @isset($definition['placeholder']) placeholder="{{ $definition['placeholder'] }}" @endisset
                                   @if(in_array($definition['input'], ['tel', 'email', 'url'], true)) dir="ltr" @endif>
                        @endif
                        @if(! empty($definition['help']))<p class="uh-hint">{{ $definition['help'] }}</p>@endif
                        @if($error)<p class="uh-error">{{ $error }}</p>@endif
                    </div>
                @endif
            @endforeach
        </div>
    </section>
@endforeach

@error('settings')<p class="uh-error">{{ $message }}</p>@enderror
