@php
    $groupLabels = $groupLabels ?? [
        'branding' => 'Branding',
        'contact' => 'Contact details',
        'leads' => 'Enquiries and follow-up',
        'listings' => 'How listings appear',
    ];
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
                    $error = $errors->first('settings.'.$key) ?: $errors->first('settings.'.$key.'.*');
                @endphp
                @if($definition['input'] === 'checkbox')
                    <div class="uh-field sm:col-span-2">
                        <input type="hidden" name="settings[{{ $key }}]" value="0">
                        <label class="uh-check">
                            <input type="checkbox" id="{{ $fieldId }}" name="settings[{{ $key }}]" value="1" @checked(filter_var($current, FILTER_VALIDATE_BOOLEAN))>
                            <span>{{ $definition['label'] }}</span>
                        </label>
                        @if($error)<p class="uh-error">{{ $error }}</p>@endif
                    </div>
                @else
                    <div @class(['uh-field', 'sm:col-span-2' => in_array($definition['input'], ['textarea', 'lines'], true)])>
                        <label class="uh-label" for="{{ $fieldId }}">{{ $definition['label'] }}</label>
                        @if($definition['input'] === 'textarea')
                            <textarea id="{{ $fieldId }}" class="uh-textarea" rows="3" name="settings[{{ $key }}]" maxlength="500">{{ $current ?: ($key === 'consent_text' ? \App\Support\SettingsSchema::defaultConsentText() : '') }}</textarea>
                        @elseif($definition['input'] === 'lines')
                            <textarea id="{{ $fieldId }}" class="uh-textarea font-mono text-xs" rows="4" name="settings[{{ $key }}]" dir="ltr">{{ is_array($current) ? implode("\n", $current) : $current }}</textarea>
                        @elseif($definition['input'] === 'select')
                            <select id="{{ $fieldId }}" class="uh-select" name="settings[{{ $key }}]">
                                @foreach($definition['options'] as $value => $label)
                                    <option value="{{ $value }}" @selected($current === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        @else
                            <input id="{{ $fieldId }}" class="uh-input" name="settings[{{ $key }}]" value="{{ is_scalar($current) ? $current : '' }}"
                                   type="{{ $definition['input'] }}" @if(in_array($definition['input'], ['tel', 'email'], true)) dir="ltr" @endif>
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
