@extends('layouts.admin')
@section('title', $title)

@php
    $tabs = [
        'general' => ['General', 'admin.settings.index'],
        'theme' => ['Theme', 'admin.settings.theme'],
        'contact' => ['Contact & social', 'admin.settings.contact'],
        'enquiries' => ['Enquiries', 'admin.settings.enquiries'],
        'listings' => ['Listings', 'admin.listing-display'],
        'analytics' => ['Analytics', 'admin.settings.analytics'],
        'seo' => ['SEO', 'admin.settings.seo'],
        'copy' => ['Website copy', 'admin.settings.copy'],
    ];
    $hasImages = collect($definitions)->flatten(1)->contains(fn ($definition) => ($definition['input'] ?? null) === 'image');
@endphp

@section('content')
    <x-ui.page-header compact :title="$title">
        <x-slot:eyebrow>Website settings</x-slot:eyebrow>
    </x-ui.page-header>

    <x-ui.admin-tabs label="Settings sections" class="mb-5">
        @foreach($tabs as $key => [$label, $route])
            <a href="{{ route($route) }}" @class(['is-active' => $screen === $key]) @if($screen === $key) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </x-ui.admin-tabs>

    @if($screen === 'analytics')
        <section class="dd-status-grid mb-5" aria-label="Tracking status">
            @foreach($tracking as $row)
                <div class="dd-status-card">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-semibold text-[var(--dd-muted)]">{{ $row['label'] }}</span>
                        <x-ui.badge :tone="$row['tone']">{{ $row['state'] }}</x-ui.badge>
                    </div>
                    <p class="mt-2 truncate text-sm font-medium" dir="auto" title="{{ $row['detail'] }}">{{ $row['detail'] }}</p>
                </div>
            @endforeach
        </section>
    @endif

    @if($screen === 'enquiries')
        <section class="uh-panel mb-5" aria-labelledby="email-heading">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-start gap-3">
                    <span @class(['dd-status-dot mt-1.5', 'is-ok' => $mailConfigured])></span>
                    <div>
                        <h2 id="email-heading" class="uh-h4">Email delivery</h2>
                        <p class="mt-1 text-sm {{ $mailConfigured ? 'text-[var(--color-muted)]' : 'font-semibold text-[var(--color-danger)]' }}">
                            {{ $mailConfigured ? 'Configured. Send a test to confirm the server can deliver.' : 'Not configured. Staff alerts and enquiry acknowledgements are not being sent.' }}
                        </p>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.settings.test-email') }}">
                    @csrf
                    <button type="submit" class="uh-btn-outline uh-btn-sm" @disabled(! $mailConfigured)>
                        <x-icon name="mail" class="size-4" /> Send a test email to me
                    </button>
                </form>
            </div>
            @error('mail')<p class="uh-error mt-2">{{ $message }}</p>@enderror
        </section>
    @endif

    @if($screen === 'theme')
        @include('admin.settings.theme')
    @else
        <form method="POST" action="{{ route('admin.settings.update') }}" class="min-w-0" x-data="uhForm" @submit="submit"
              @if($hasImages) enctype="multipart/form-data" @endif data-unsaved-guard>
            @csrf
            @method('PUT')
            <div class="uh-admin-stack">
                @include('admin.settings._fields')
            </div>

            <div class="uh-admin-dock">
                <button type="submit" class="uh-btn-primary" :disabled="submitting">
                    <span class="uh-spinner" x-show="submitting" x-cloak></span>
                    <span>Save {{ strtolower($tabs[$screen][0]) }} settings</span>
                </button>
            </div>
        </form>
    @endif

    @if($screen === 'contact')
        <form method="POST" action="{{ route('admin.settings.social') }}" class="uh-panel mt-6" x-data="uhForm" @submit="submit" data-unsaved-guard
              aria-labelledby="social-heading">
            @csrf
            @method('PUT')
            @php
                $profiles = collect(old('profiles', $socialProfiles))->map(fn ($profile) => [
                    'platform' => $profile['platform'] ?? 'website',
                    'label' => $profile['label'] ?? '',
                    'url' => $profile['url'] ?? '',
                    'active' => filter_var($profile['active'] ?? true, FILTER_VALIDATE_BOOLEAN),
                    'new_tab' => filter_var($profile['new_tab'] ?? true, FILTER_VALIDATE_BOOLEAN),
                ])->values()->all();
            @endphp
            <div x-data="{ profiles: @js($profiles), max: {{ \App\Support\SocialProfiles::MAX }} }">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 id="social-heading" class="uh-h4">Social profiles</h2>
                    </div>
                    <button type="button" class="uh-btn-outline uh-btn-sm" @click="profiles.push({ platform: 'facebook', label: '', url: '', active: true, new_tab: true })" x-show="profiles.length < max">
                        <x-icon name="plus" class="size-4" /> Add profile
                    </button>
                </div>

                <ol class="dd-repeater mt-4">
                    <template x-for="(profile, index) in profiles" :key="index">
                        <li class="dd-social-row">
                            <select class="uh-select" data-native-select :name="`profiles[${index}][platform]`" x-model="profile.platform" aria-label="Platform">
                                @foreach($socialPlatforms as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <input class="uh-input" type="url" dir="ltr" :name="`profiles[${index}][url]`" x-model="profile.url" placeholder="https://facebook.com/yourpage" required aria-label="Profile link">
                            <input class="uh-input" type="text" :name="`profiles[${index}][label]`" x-model="profile.label" maxlength="60" placeholder="Label (optional)" aria-label="Label">
                            <input type="hidden" :name="`profiles[${index}][active]`" :value="profile.active ? 1 : 0">
                            <input type="hidden" :name="`profiles[${index}][new_tab]`" :value="profile.new_tab ? 1 : 0">
                            <label class="uh-check whitespace-nowrap text-xs"><input type="checkbox" x-model="profile.new_tab"> <span>New tab</span></label>
                            <label class="dd-switch" :title="profile.active ? 'Shown' : 'Hidden'">
                                <input type="checkbox" x-model="profile.active">
                                <span class="dd-switch-track" aria-hidden="true"></span>
                                <span class="sr-only">Show on the website</span>
                            </label>
                            <span class="flex">
                                <button type="button" class="uh-admin-icon-btn" :disabled="index === 0" @click="profiles.splice(index - 1, 0, profiles.splice(index, 1)[0])" aria-label="Move up"><x-icon name="chevron-down" class="size-4 rotate-180" /></button>
                                <button type="button" class="uh-admin-icon-btn" :disabled="index === profiles.length - 1" @click="profiles.splice(index + 1, 0, profiles.splice(index, 1)[0])" aria-label="Move down"><x-icon name="chevron-down" class="size-4" /></button>
                            </span>
                            <button type="button" class="uh-admin-icon-btn text-[var(--dd-red)]" @click="profiles.splice(index, 1)" aria-label="Remove profile"><x-icon name="trash" class="size-4" /></button>
                        </li>
                    </template>
                </ol>
                <p class="text-sm text-[var(--color-muted)]" x-show="profiles.length === 0">No social profiles yet.</p>
                @if($errors->has('profiles') || $errors->has('profiles.*'))
                    <p class="uh-error mt-2">{{ $errors->first('profiles') ?: collect($errors->get('profiles.*'))->flatten()->first() }}</p>
                @endif

                <div class="dd-form-footer">
                    <button type="submit" class="uh-btn-primary" :disabled="submitting">
                        <span class="uh-spinner" x-show="submitting" x-cloak></span>
                        Save social profiles
                    </button>
                </div>
            </div>
        </form>
    @endif
@endsection
