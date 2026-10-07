@php
    $user = auth()->user();
    $can = fn (string $permission): bool => (bool) $user?->hasPermission($permission);
    $canReview = $can('property.publish') || $can('project.publish');
    $canSettings = (bool) $user?->can('settings.update');
    $canReference = (bool) $user?->can('reference.manage');
    $pageTitle = trim($__env->yieldContent('title') ?: 'Dashboard');
    $settingsUrl = $canSettings ? route('admin.settings.index') : null;

    $navGroups = [
        'Menu' => [
            ['pattern' => 'admin.dashboard', 'label' => 'Dashboard', 'url' => route('admin.dashboard'), 'icon' => 'dashboard'],
            ['pattern' => 'admin.notifications.*', 'label' => 'Report', 'url' => route('admin.notifications.index'), 'icon' => 'list'],
            ['pattern' => 'admin.properties.*', 'label' => 'Properties', 'url' => $can('property.view') ? route('admin.properties.index') : route('admin.dashboard'), 'icon' => 'home'],
            ['pattern' => 'admin.leads.*', 'label' => 'Consumer', 'url' => $can('lead.view') ? route('admin.leads.index') : route('admin.dashboard'), 'icon' => 'users'],
        ],
        'Financial' => array_values(array_filter([
            $can('lead.view') ? ['pattern' => 'admin.follow-ups.*', 'label' => 'Transactions', 'url' => route('admin.follow-ups.index'), 'icon' => 'document'] : null,
            $can('lead.view') ? ['pattern' => 'admin.visits.*', 'label' => 'Invoices', 'url' => route('admin.visits.index'), 'icon' => 'inbox'] : null,
        ])),
        'Tools' => array_values(array_filter([
            $canSettings ? ['pattern' => 'admin.settings.*', 'label' => 'Settings', 'url' => $settingsUrl, 'icon' => 'settings'] : null,
            $user?->can('viewAny', App\Models\User::class) ? ['pattern' => 'admin.staff.*', 'label' => 'Feedback', 'url' => route('admin.staff.index'), 'icon' => 'check-circle'] : null,
            ['pattern' => 'admin.audit.*', 'label' => 'Help', 'url' => $user?->can('audit.view') ? route('admin.audit.index') : route('admin.dashboard'), 'icon' => 'info'],
        ])),
    ];

    $navGroups = array_filter($navGroups, fn($items) => count($items) > 0);

    $openGroups = [];
    foreach ($navGroups as $group => $items) {
        foreach ($items as $item) {
            if (request()->routeIs($item['pattern'])) {
                $openGroups[] = $group;
            }
        }
    }

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

    $quickAddAreas = $user?->can('create', App\Models\Project::class)
        ? App\Models\LocationArea::query()->active()->orderBy('name')->get(['id', 'name', 'city'])
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
        <title>{{ $pageTitle }} — Urban Haven</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="uh-admin antialiased"
          x-data="uhAdminShell(@js(array_values(array_unique($openGroups))))"
          :class="{ 'uh-admin-collapsed': collapsed }"
          @uh-quick-add.window="open($event.detail)"
          @keydown.escape="drawer ? close() : closeMobile()">
        <a class="uh-skip" href="#main">Skip to content</a>

        <div class="dd-app-shell">

            {{-- Mobile backdrop --}}
            <div class="uh-admin-backdrop" x-show="mobile" x-cloak @click="closeMobile()" aria-hidden="true"></div>

            {{-- ===================== SIDEBAR ===================== --}}
            <aside id="admin-nav" class="dd-sidebar" :class="{ 'is-open': mobile }" aria-label="Staff navigation">

                {{-- Brand --}}
                <div class="dd-sidebar-brand">
                    <a href="{{ route('admin.dashboard') }}" class="dd-brand-link">
                        <span class="dd-brand-mark" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M3 10L10 3L17 10V17H13V13H7V17H3V10Z" fill="white"/>
                            </svg>
                        </span>
                        <span class="dd-brand-name">Urban Haven</span>
                    </a>
                    <button type="button" class="dd-sidebar-close uh-admin-icon-btn" @click="closeMobile()" aria-label="Close navigation">
                        <x-icon name="close" class="size-5" />
                    </button>
                </div>

                {{-- Navigation --}}
                <nav class="dd-nav" x-data>
                    @foreach($navGroups as $group => $items)
                        @continue(count($items) === 0)
                        <div class="dd-nav-group">
                            <span class="dd-nav-group-label">{{ strtoupper($group) }}</span>
                            <ul class="dd-nav-list">
                                @foreach($items as $item)
                                    @php($current = request()->routeIs($item['pattern']))
                                    <li>
                                        <a href="{{ $item['url'] }}"
                                           title="{{ $item['label'] }}"
                                           @if($current) aria-current="page" @endif
                                           @class(['dd-nav-link', 'dd-nav-link-active' => $current])>
                                            <x-icon :name="$item['icon']" class="dd-nav-icon" />
                                            <span class="dd-nav-label">{{ $item['label'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </nav>



            </aside>

            {{-- ===================== MAIN SHELL ===================== --}}
            <div class="dd-shell">

                {{-- Header --}}
                <header class="dd-header">
                    <div class="dd-header-start">
                        <button type="button" class="uh-admin-icon-btn dd-mobile-toggle" @click="openMobile()"
                                :aria-expanded="mobile.toString()" aria-controls="admin-nav" aria-label="Open navigation">
                            <x-icon name="menu" class="size-5" />
                        </button>
                        <button type="button" class="uh-admin-icon-btn dd-desktop-toggle" @click="toggleCollapsed()"
                                aria-label="Toggle sidebar">
                            <x-icon name="menu" class="size-5" />
                        </button>
                    </div>

                    <div class="dd-header-end">
                        <button type="button" class="dd-header-icon-btn" aria-label="Search">
                            <x-icon name="search" class="size-4" />
                        </button>
                        @include('admin.notifications._bell')

                        <div class="dd-user-widget" x-data="{ open: false }" @keydown.escape="open = false">
                            <button type="button" class="dd-user-btn" @click="open = ! open" :aria-expanded="open.toString()">
                                <span class="dd-avatar">{{ $initials ?: 'S' }}</span>
                                <span class="dd-user-info">
                                    <span class="dd-user-name">{{ $user->name }}</span>
                                    <span class="dd-user-role">{{ $user->roles->pluck('label')->first() ?: 'Admin store' }}</span>
                                </span>
                            </button>
                            <div class="uh-admin-user-menu" x-show="open" x-cloak x-transition.opacity.duration.120ms @click.outside="open = false">
                                <p class="uh-admin-user-menu-id">
                                    <span class="font-semibold text-ink">{{ $user->name }}</span>
                                    <span class="mt-0.5 block truncate text-xs text-[var(--color-muted)]">{{ $user->email }}</span>
                                </p>
                                <a href="{{ route('home') }}">View public site</a>
                                <form method="POST" action="{{ route('admin.logout') }}">
                                    @csrf
                                    <button type="submit">Sign out</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </header>

                {{-- Content --}}
                <main id="main" class="dd-content">
                    @foreach($systemWarnings as $warning)
                        <x-ui.alert tone="warn" class="mb-3" style="border-radius:0.75rem; font-size:0.8rem;">{{ $warning }}</x-ui.alert>
                    @endforeach
                    <x-ui.flash />
                    @yield('content')
                </main>
            </div>
        </div>

        @include('admin.partials.quick-add')
    </body>
</html>
