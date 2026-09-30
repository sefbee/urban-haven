@extends('layouts.admin')
@section('title', 'Settings')

@php($grouped = $settings->groupBy(fn ($setting) => $setting->group ?: 'general'))

@section('content')
    <x-ui.page-header compact title="Settings"
                      description="Contact details, analytics and the reference lists used across the site." />

    <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-7 space-y-6" x-data="uhForm" @submit="submit">
        @csrf
        @method('PUT')

        @foreach($grouped as $group => $items)
            <section class="uh-panel">
                <h2 class="uh-h4">{{ \Illuminate\Support\Str::of($group)->replace('_', ' ')->headline() }}</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    @foreach($items as $setting)
                        @php($fieldId = 'setting-'.\Illuminate\Support\Str::slug($setting->key))
                        @if(in_array($setting->cast, ['bool', 'boolean'], true))
                            <div class="uh-field sm:col-span-2">
                                <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                                <label class="uh-check">
                                    <input type="checkbox" id="{{ $fieldId }}" name="settings[{{ $setting->key }}]" value="1"
                                           @checked($setting->castedValue())>
                                    <span>{{ \Illuminate\Support\Str::of($setting->key)->replace('_', ' ')->ucfirst() }}</span>
                                </label>
                            </div>
                        @elseif(mb_strlen((string) $setting->value) > 90 || in_array($setting->cast, ['json', 'array'], true))
                            <div class="uh-field sm:col-span-2">
                                <label class="uh-label" for="{{ $fieldId }}">
                                    {{ \Illuminate\Support\Str::of($setting->key)->replace('_', ' ')->ucfirst() }}
                                </label>
                                <textarea id="{{ $fieldId }}" class="uh-textarea font-mono text-xs" rows="4"
                                          name="settings[{{ $setting->key }}]">{{ $setting->value }}</textarea>
                            </div>
                        @else
                            <div class="uh-field">
                                <label class="uh-label" for="{{ $fieldId }}">
                                    {{ \Illuminate\Support\Str::of($setting->key)->replace('_', ' ')->ucfirst() }}
                                </label>
                                <input id="{{ $fieldId }}" class="uh-input" name="settings[{{ $setting->key }}]"
                                       value="{{ $setting->value }}" dir="ltr">
                            </div>
                        @endif
                    @endforeach
                </div>
            </section>
        @endforeach

        <button type="submit" class="uh-btn-primary" :disabled="submitting">
            <span class="uh-spinner" x-show="submitting" x-cloak></span>
            <span x-text="submitting ? 'Saving…' : 'Save settings'">Save settings</span>
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
                    <button type="submit" class="uh-btn-outline uh-btn-sm uh-btn-block">
                        <x-icon name="plus" class="size-3.5" />
                        Add type
                    </button>
                </form>

                @if($types->isNotEmpty())
                    <ul class="mt-5 divide-y divide-[var(--color-line)] border-t border-line pt-1 text-sm">
                        @foreach($types as $type)
                            <li class="flex items-center justify-between gap-2 py-2">
                                <span @class(['min-w-0 truncate', 'text-[var(--color-muted)]' => ! $type->is_active])>{{ $type->label }}</span>
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
