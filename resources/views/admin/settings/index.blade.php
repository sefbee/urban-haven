@extends('layouts.admin')
@section('title', 'Settings')

@php
    $groupLabels = ['branding' => 'Branding', 'contact' => 'Contact details', 'leads' => 'Enquiries and follow-up', 'listings' => 'Listings and maps'];
    $analytics = array_filter([
        'Google Tag Manager' => config('urbanhaven.analytics.gtm_id'),
        'Google Analytics 4' => config('urbanhaven.analytics.ga4_id'),
        'Meta Pixel' => config('urbanhaven.analytics.meta_pixel_id'),
    ]);
@endphp

@section('content')
    <x-ui.page-header compact title="Settings"
                      description="Contact details, enquiry handling, listing display and the reference lists used across the site." />

    <section class="uh-panel mt-7" aria-labelledby="email-heading">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 id="email-heading" class="uh-h4">Email delivery</h2>
                <p class="mt-1 text-sm {{ $mailConfigured ? 'text-[var(--color-muted)]' : 'font-semibold text-[var(--color-danger)]' }}">
                    {{ $mailConfigured ? 'Configured. Send a test to confirm the server can deliver.' : 'Not configured. Staff alerts and enquiry acknowledgements are not being sent.' }}
                </p>
            </div>
            <form method="POST" action="{{ route('admin.settings.test-email') }}">
                @csrf
                <button type="submit" class="uh-btn-outline uh-btn-sm" @disabled(! $mailConfigured)>Send a test email to me</button>
            </form>
        </div>
        @error('mail')<p class="uh-error mt-2">{{ $message }}</p>@enderror
        <p class="mt-3 text-xs text-[var(--color-muted)]">
            Analytics: {{ $analytics !== [] ? implode(', ', array_keys($analytics)).' configured, loaded only after visitors accept cookies.' : 'no tracking IDs configured. Set them in the server environment to enable measurement.' }}
        </p>
    </section>

    <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-6 space-y-6" x-data="uhForm" @submit="submit">
        @csrf
        @method('PUT')

        @foreach($definitions as $group => $items)
            <section class="uh-panel">
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

        <button type="submit" class="uh-btn-primary" :disabled="submitting">
            <span class="uh-spinner" x-show="submitting" x-cloak></span>
            <span>Save settings</span>
        </button>
    </form>

    <section class="mt-10" aria-labelledby="reference-heading">
        <h2 id="reference-heading" class="uh-h3">Reference data</h2>
        <p class="mt-2 max-w-2xl text-sm text-[var(--color-muted)]">
            Areas, property types and amenities power the public search filters. Deactivating one hides it from filters without touching existing listings.
        </p>

        <div class="mt-4 grid gap-4 lg:grid-cols-3">
            {{-- Areas --}}
            <div class="uh-panel">
                <h3 class="uh-h4">Areas</h3>
                <form method="POST" action="{{ route('admin.settings.areas.store') }}" class="mt-4 space-y-3">
                    @csrf
                    <x-ui.input name="name" id="area-name" label="Area name" required placeholder="Gulshan 2" />
                    <x-ui.input name="city" id="area-city" label="City" required value="Dhaka" />
                    <button type="submit" class="uh-btn-outline uh-btn-sm uh-btn-block">
                        <x-icon name="plus" class="size-3.5" />
                        Add area
                    </button>
                </form>

                @if($areas->isNotEmpty())
                    <ul class="mt-5 divide-y divide-[var(--color-line)] border-t border-line pt-1 text-sm">
                        @foreach($areas as $area)
                            <li class="flex items-center justify-between gap-2 py-2">
                                <span @class(['min-w-0 truncate', 'text-[var(--color-muted)]' => ! $area->is_active])>
                                    {{ $area->name }}
                                    <span class="text-xs text-[var(--color-muted)]">· {{ $area->city }}</span>
                                </span>
                                <details class="shrink-0 text-right">
                                    <summary class="uh-link-quiet cursor-pointer text-xs">Edit</summary>
                                    <form method="POST" action="{{ route('admin.settings.areas.update', $area) }}" class="mt-2 space-y-2 text-left">
                                        @csrf
                                        @method('PUT')
                                        <x-ui.input name="name" label="Name" :value="$area->name" required :id="'an-'.$area->id" />
                                        <x-ui.input name="city" label="City" :value="$area->city" required :id="'ac-'.$area->id" />
                                        <x-ui.textarea name="intro" label="Area page introduction" rows="3" :value="$area->intro" optional :id="'ai-'.$area->id" />
                                        <x-ui.input name="meta_description" label="Meta description" :value="$area->meta_description" maxlength="160" optional :id="'am-'.$area->id" />
                                        <div class="grid grid-cols-2 gap-2">
                                            <x-ui.input name="lat" label="Latitude" type="number" step="0.0000001" :value="$area->lat" optional :id="'alat-'.$area->id" />
                                            <x-ui.input name="lng" label="Longitude" type="number" step="0.0000001" :value="$area->lng" optional :id="'alng-'.$area->id" />
                                        </div>
                                        <input type="hidden" name="is_active" value="0">
                                        <label class="uh-check text-xs"><input type="checkbox" name="is_active" value="1" @checked($area->is_active)> <span>Active</span></label>
                                        <button type="submit" class="uh-btn-outline uh-btn-sm">Save area</button>
                                    </form>
                                </details>
                                @if($area->is_active)
                                    <form method="POST" action="{{ route('admin.settings.areas.deactivate', $area) }}"
                                          x-data="uhConfirm('Deactivate {{ $area->name }}?')">
                                        @csrf
                                        <button type="submit" class="uh-link-quiet text-xs text-[var(--color-danger)]" @click="confirm($event)">
                                            Deactivate
                                        </button>
                                    </form>
                                @else
                                    <x-ui.badge tone="outline">Inactive</x-ui.badge>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Types --}}
            <div class="uh-panel">
                <h3 class="uh-h4">Property types</h3>
                <form method="POST" action="{{ route('admin.settings.types.store') }}" class="mt-4 space-y-3">
                    @csrf
                    <x-ui.input name="key" id="type-key" label="Key" required placeholder="apartment" dir="ltr"
                                hint="Lowercase, no spaces. Used internally." />
                    <x-ui.input name="label" id="type-label" label="Label" required placeholder="Apartment" />
                    <x-ui.select name="category" id="type-category" label="Category">
                        @foreach($categories as $category)
                            <option value="{{ $category }}">{{ ucfirst($category) }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select name="field_profile" id="type-profile" label="Fields shown" hint="Plots hide bedrooms, bathrooms, balconies and floor.">
                        @foreach($profiles as $profile)
                            <option value="{{ $profile }}">{{ ucfirst($profile) }}</option>
                        @endforeach
                    </x-ui.select>
                    <button type="submit" class="uh-btn-outline uh-btn-sm uh-btn-block">
                        <x-icon name="plus" class="size-3.5" />
                        Add type
                    </button>
                </form>

                @if($types->isNotEmpty())
                    <ul class="mt-5 divide-y divide-[var(--color-line)] border-t border-line pt-1 text-sm">
                        @foreach($types as $type)
                            <li class="flex items-center justify-between gap-2 py-2">
                                <span @class(['min-w-0 truncate', 'text-[var(--color-muted)]' => ! $type->is_active])>{{ $type->label }} <span class="text-xs text-[var(--color-muted)]">· {{ $type->field_profile }}</span></span>
                                <details class="shrink-0 text-right">
                                    <summary class="uh-link-quiet cursor-pointer text-xs">Edit</summary>
                                    <form method="POST" action="{{ route('admin.settings.types.update', $type) }}" class="mt-2 space-y-2 text-left">
                                        @csrf
                                        @method('PUT')
                                        <x-ui.input name="label" label="Label" :value="$type->label" required :id="'tl-'.$type->id" />
                                        <x-ui.select name="category" label="Category" :id="'tc-'.$type->id">
                                            @foreach($categories as $category)
                                                <option value="{{ $category }}" @selected($type->category === $category)>{{ ucfirst($category) }}</option>
                                            @endforeach
                                        </x-ui.select>
                                        <x-ui.select name="field_profile" label="Fields shown" :id="'tp-'.$type->id">
                                            @foreach($profiles as $profile)
                                                <option value="{{ $profile }}" @selected($type->field_profile === $profile)>{{ ucfirst($profile) }}</option>
                                            @endforeach
                                        </x-ui.select>
                                        <input type="hidden" name="is_active" value="0">
                                        <label class="uh-check text-xs"><input type="checkbox" name="is_active" value="1" @checked($type->is_active)> <span>Active</span></label>
                                        <button type="submit" class="uh-btn-outline uh-btn-sm">Save type</button>
                                    </form>
                                </details>
                                @if($type->is_active)
                                    <form method="POST" action="{{ route('admin.settings.types.deactivate', $type) }}"
                                          x-data="uhConfirm('Deactivate {{ $type->label }}?')">
                                        @csrf
                                        <button type="submit" class="uh-link-quiet text-xs text-[var(--color-danger)]" @click="confirm($event)">
                                            Deactivate
                                        </button>
                                    </form>
                                @else
                                    <x-ui.badge tone="outline">Inactive</x-ui.badge>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Amenities --}}
            <div class="uh-panel">
                <h3 class="uh-h4">Amenities</h3>
                <form method="POST" action="{{ route('admin.settings.amenities.store') }}" class="mt-4 space-y-3">
                    @csrf
                    <x-ui.input name="key" id="amenity-key" label="Key" required placeholder="lift" dir="ltr"
                                hint="Lowercase, no spaces. Used internally." />
                    <x-ui.input name="label" id="amenity-label" label="Label" required placeholder="Lift" />
                    <button type="submit" class="uh-btn-outline uh-btn-sm uh-btn-block">
                        <x-icon name="plus" class="size-3.5" />
                        Add amenity
                    </button>
                </form>

                @if($amenities->isNotEmpty())
                    <ul class="mt-5 divide-y divide-[var(--color-line)] border-t border-line pt-1 text-sm">
                        @foreach($amenities as $amenity)
                            <li class="flex items-center justify-between gap-2 py-2">
                                <span @class(['min-w-0 truncate', 'text-[var(--color-muted)]' => ! $amenity->is_active])>{{ $amenity->label }}</span>
                                @if($amenity->is_active)
                                    <form method="POST" action="{{ route('admin.settings.amenities.deactivate', $amenity) }}"
                                          x-data="uhConfirm('Deactivate {{ $amenity->label }}?')">
                                        @csrf
                                        <button type="submit" class="uh-link-quiet text-xs text-[var(--color-danger)]" @click="confirm($event)">
                                            Deactivate
                                        </button>
                                    </form>
                                @else
                                    <x-ui.badge tone="outline">Inactive</x-ui.badge>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </section>
@endsection
