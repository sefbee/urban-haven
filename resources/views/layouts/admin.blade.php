@php
    $user = auth()->user();
    $navGroups = \App\Support\AdminNavigation::for($user);
    $currentNav = \App\Support\AdminNavigation::current($navGroups);
    $pageTitle = html_entity_decode(trim($__env->yieldContent('title') ?: ($currentNav['item']['label'] ?? 'Dashboard')), ENT_QUOTES);
    $companyName = \App\Models\Setting::get('company_name') ?: 'Urban Haven';
    $brandLogo = \App\Models\Setting::get('brand_logo');
    $sidebarLogo = \App\Models\Setting::get('brand_logo_dark');
    $companyTagline = \App\Models\Setting::get('company_tagline');
    $brandFavicon = \App\Models\Setting::get('brand_favicon');

    $systemWarnings = [];
    if ($user?->isOwnerAdmin()) {
        if (! \App\Support\HealthStatus::mailConfigured()) {
            $systemWarnings[] = 'Email is not configured, so staff alerts and enquiry acknowledgements are not being sent.';
        }
        $failedJobs = \Illuminate\Support\Facades\Cache::remember('uh:admin:failed-jobs', 300, fn (): int => \Illuminate\Support\Facades\Schema::hasTable('failed_jobs') ? \Illuminate\Support\Facades\DB::table('failed_jobs')->count() : 0);
        if ($failedJobs > 0) {
            $systemWarnings[] = trans_choice(':count background job has failed.|:count background jobs have failed.', $failedJobs);
        }
    }

    $quickAddAreas = $user?->can('create', App\Models\Project::class) || $user?->can('reference.manage')
        ? App\Models\LocationArea::query()->active()->orderBy('name')->get(['id', 'name', 'city', 'country'])
        : collect();

    $initials = collect(preg_split('/\s+/', trim((string) $user?->name)))
        ->filter()
        ->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
        ->take(2)
        ->implode('');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">
        <title>{{ $pageTitle }} — {{ $companyName }} admin</title>
        @if($brandFavicon)
            <link rel="icon" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($brandFavicon) }}">
        @endif
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="uh-admin antialiased"
          x-data="uhAdminShell([])"
          :class="{ 'uh-admin-collapsed': collapsed }"
          @uh-quick-add.window="open($event.detail)"
          @keydown.escape="drawer ? close() : closeMobile()">
        <a class="uh-skip" href="#main">Skip to content</a>

        <div class="dd-app-shell flex h-screen w-full overflow-hidden">
            <div class="uh-admin-backdrop" x-show="mobile" x-cloak @click="closeMobile()" aria-hidden="true"></div>

            <aside id="admin-nav" class="dd-sidebar" :class="{ 'is-open': mobile }" aria-label="Staff navigation">
                <div class="dd-sidebar-brand">
                    <a href="{{ route('admin.dashboard') }}" class="dd-brand-link" title="{{ $companyName }}">
                        @if($sidebarLogo)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($sidebarLogo) }}" alt="{{ $companyName }}" class="dd-brand-logo">
                        @elseif($brandLogo)
                            <span class="dd-brand-chip">
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($brandLogo) }}" alt="{{ $companyName }}" class="dd-brand-logo">
                            </span>
                        @else
                            <span class="dd-brand-mark" aria-hidden="true">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <path d="M3 10L10 3L17 10V17H13V13H7V17H3V10Z" fill="currentColor"/>
                                </svg>
                            </span>
                            <span class="dd-brand-copy">
                                <span class="dd-brand-name">{{ $companyName }}</span>
                                @if($companyTagline)
                                    <span class="dd-brand-tagline">{{ $companyTagline }}</span>
                                @endif
                            </span>
                        @endif
                    </a>
                    <button type="button" class="dd-sidebar-close uh-admin-icon-btn" @click="closeMobile()" aria-label="Close navigation">
                        <x-icon name="close" class="size-5" />
                    </button>
                </div>

                <nav class="dd-nav" x-init="$nextTick(() => {
                    const active = $el.querySelector('[aria-current=page]');
                    if (active) {
                        $el.scrollTop += active.getBoundingClientRect().top - $el.getBoundingClientRect().top - ($el.clientHeight - active.offsetHeight) / 2;
                    }
                })">
                    @foreach($navGroups as $group => $items)
                        <div class="dd-nav-group">
                            <span class="dd-nav-group-label">{{ $group }}</span>
                            <ul class="dd-nav-list">
                                @foreach($items as $item)
                                    @php $current = request()->routeIs(...(array) $item['pattern']); @endphp
                                    <li>
                                        <a href="{{ $item['url'] }}"
                                           title="{{ $item['label'] }}"
                                           @if($current) aria-current="page" @endif
                                           @class(['dd-nav-link', 'dd-nav-link-active' => $current])>
                                            <span class="dd-nav-tile"><x-icon :name="$item['icon']" class="dd-nav-icon" /></span>
                                            <span class="dd-nav-label">{{ $item['label'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </nav>

                <div class="dd-sidebar-foot">
                    <a href="{{ route('home') }}" target="_blank" rel="noopener" class="dd-nav-link dd-live-link" title="View live site">
                        <span class="dd-nav-tile"><x-icon name="external" class="dd-nav-icon" /></span>
                        <span class="dd-nav-label">View live site</span>
                    </a>
                </div>
            </aside>

            <div class="dd-shell flex min-h-0 min-w-0 flex-1 flex-col">
                <header class="dd-header">
                    <div class="dd-header-start">
                        <button type="button" class="dd-header-icon-btn dd-mobile-toggle" @click="openMobile()"
                                :aria-expanded="mobile.toString()" aria-controls="admin-nav" aria-label="Open navigation">
                            <x-icon name="menu" class="size-4" />
                        </button>
                        <button type="button" class="dd-header-icon-btn dd-desktop-toggle" @click="toggleCollapsed()"
                                :aria-pressed="collapsed.toString()" aria-label="Collapse sidebar">
                            <x-icon name="menu" class="size-4" />
                        </button>
                        <nav class="dd-breadcrumb" aria-label="Breadcrumb">
                            @if($currentNav)
                                <span class="dd-breadcrumb-group">{{ $currentNav['group'] }}</span>
                                <x-icon name="chevron-right" class="size-3 text-[var(--dd-faint)]" />
                                <a href="{{ $currentNav['item']['url'] }}" class="dd-breadcrumb-current">{{ $currentNav['item']['label'] }}</a>
                            @else
                                <span class="dd-breadcrumb-current">{{ $pageTitle }}</span>
                            @endif
                        </nav>
                    </div>

                    <div class="dd-header-end">
                        <form method="GET" action="{{ route('admin.search') }}" class="dd-header-search" role="search"
                              x-data @keydown.window.slash="if (! ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName) && ! document.activeElement.isContentEditable) { $event.preventDefault(); $refs.search.focus() }">
                            <x-icon name="search" class="dd-header-search-icon" />
                            <label for="admin-search" class="sr-only">Search the admin</label>
                            <input id="admin-search" x-ref="search" type="search" name="q" value="{{ request()->routeIs('admin.search') ? request('q') : '' }}"
                                   placeholder="Search properties, leads, pages…" autocomplete="off" maxlength="120">
                            <kbd class="dd-header-search-kbd" aria-hidden="true">/</kbd>
                        </form>
                        <a href="{{ route('admin.search') }}" class="dd-header-icon-btn dd-header-search-btn" aria-label="Search">
                            <x-icon name="search" class="size-4" />
                        </a>
                        @include('admin.notifications._bell')

                        <div class="dd-user-widget" x-data="{ open: false }" @keydown.escape="open = false">
                            <button type="button" class="dd-user-btn" @click="open = ! open" :aria-expanded="open.toString()" aria-haspopup="menu">
                                <span class="dd-avatar">{{ $initials ?: 'S' }}</span>
                                <span class="dd-user-info">
                                    <span class="dd-user-name">{{ $user->name }}</span>
                                    <span class="dd-user-role">{{ $user->roles->pluck('label')->first() ?: 'Staff' }}</span>
                                </span>
                                <x-icon name="chevron-down" class="size-3.5 text-[var(--dd-muted)]" />
                            </button>
                            <div class="uh-admin-user-menu" x-show="open" x-cloak x-transition.opacity.duration.120ms @click.outside="open = false">
                                <p class="uh-admin-user-menu-id">
                                    <span class="font-semibold text-ink">{{ $user->name }}</span>
                                    <span class="mt-0.5 block truncate text-xs text-[var(--color-muted)]">{{ $user->email }}</span>
                                </p>
                                @if($user->isOwnerAdmin())
                                    <a href="{{ route('admin.settings.index') }}">Website settings</a>
                                @endif
                                <a href="{{ route('home') }}" target="_blank" rel="noopener">View public site</a>
                                <form method="POST" action="{{ route('admin.logout') }}">
                                    @csrf
                                    <button type="submit">Sign out</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </header>

                <main id="main" class="dd-content min-h-0 min-w-0 flex-1 overflow-x-hidden overflow-y-auto">
                    @foreach($systemWarnings as $warning)
                        <x-ui.alert tone="warn" class="mb-3">{{ $warning }}</x-ui.alert>
                    @endforeach
                    <x-ui.flash />
                    @yield('content')
                </main>
            </div>
        </div>

        @include('admin.partials.quick-add')
    </body>
</html>
