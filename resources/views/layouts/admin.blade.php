@php
    $user = auth()->user();
    $can = fn (string $permission): bool => (bool) $user?->hasPermission($permission);
    $canReview = $can('property.publish') || $can('project.publish');
    $canSettings = (bool) $user?->can('settings.update');
    $canReference = (bool) $user?->can('reference.manage');
    $pageTitle = trim($__env->yieldContent('title') ?: 'Dashboard');
    $settingsUrl = $canSettings ? route('admin.settings.index') : null;

    $navGroups = [
        'Desk' => [
            ['pattern' => 'admin.dashboard', 'label' => 'Dashboard', 'url' => route('admin.dashboard'), 'icon' => 'dashboard'],
            ['pattern' => 'admin.notifications.*', 'label' => 'Notifications', 'url' => route('admin.notifications.index'), 'icon' => 'bell'],
        ],
        'Inventory' => array_values(array_filter([
            $can('property.view') || $can('property.update') ? ['pattern' => 'admin.properties.*', 'label' => 'Properties', 'url' => route('admin.properties.index'), 'icon' => 'home'] : null,
            $can('project.view') || $can('project.update') ? ['pattern' => 'admin.projects.*', 'label' => 'Projects', 'url' => route('admin.projects.index'), 'icon' => 'building'] : null,
            $canReview ? ['pattern' => 'admin.review.*', 'label' => 'Review queue', 'url' => route('admin.review.index'), 'icon' => 'check-circle'] : null,
        ])),
        'Sales' => array_values(array_filter([
            $can('lead.view') ? ['pattern' => 'admin.leads.*', 'label' => 'Leads', 'url' => route('admin.leads.index'), 'icon' => 'inbox'] : null,
            $can('lead.view') ? ['pattern' => 'admin.follow-ups.*', 'label' => 'Follow-ups', 'url' => route('admin.follow-ups.index'), 'icon' => 'clock'] : null,
            $can('lead.view') ? ['pattern' => 'admin.visits.*', 'label' => 'Site visits', 'url' => route('admin.visits.index'), 'icon' => 'calendar'] : null,
        ])),
        'Website' => array_values(array_filter([
            $can('cms.update') || $can('cms.create') ? ['pattern' => 'admin.cms.*', 'label' => 'Pages', 'url' => route('admin.cms.index'), 'icon' => 'document'] : null,
            $user?->can('viewAny', App\Models\Post::class) ? ['pattern' => 'admin.posts.*', 'label' => 'Articles', 'url' => route('admin.posts.index'), 'icon' => 'list'] : null,
            $can('cms.update') || $can('cms.create') ? ['pattern' => 'admin.faqs.*', 'label' => 'FAQs', 'url' => route('admin.faqs.index'), 'icon' => 'info'] : null,
            $can('cms.publish') ? ['pattern' => 'admin.menus.*', 'label' => 'Menus', 'url' => route('admin.menus.index'), 'icon' => 'menu'] : null,
        ])),
    ];

    $catalogue = array_values(array_filter([
        $canReference ? ['pattern' => 'admin.areas.*', 'label' => 'Areas', 'url' => route('admin.areas.index'), 'icon' => 'pin'] : null,
        $canReference ? ['pattern' => 'admin.property-types.*', 'label' => 'Property types', 'url' => route('admin.property-types.index'), 'icon' => 'tag'] : null,
        $canReference ? ['pattern' => 'admin.amenities.*', 'label' => 'Amenities', 'url' => route('admin.amenities.index'), 'icon' => 'sparkle'] : null,
        $canSettings ? ['pattern' => 'admin.listing-display', 'label' => 'Listing display', 'url' => route('admin.listing-display'), 'icon' => 'map'] : null,
    ]));
    if ($catalogue !== []) {
        $navGroups['Catalogue'] = $catalogue;
    }

    $company = array_values(array_filter([
        $canSettings ? ['pattern' => 'admin.settings.*', 'label' => 'Settings', 'url' => $settingsUrl, 'icon' => 'settings'] : null,
        $user?->can('viewAny', App\Models\User::class) ? ['pattern' => 'admin.staff.*', 'label' => 'Staff', 'url' => route('admin.staff.index'), 'icon' => 'users'] : null,
        $user?->can('redirect.manage') ? ['pattern' => 'admin.redirects.*', 'label' => 'Redirects', 'url' => route('admin.redirects.index'), 'icon' => 'arrow-right'] : null,
        $user?->can('audit.view') ? ['pattern' => 'admin.audit.*', 'label' => 'Audit log', 'url' => route('admin.audit.index'), 'icon' => 'shield'] : null,
    ]));
    if ($company !== []) {
        $navGroups['Company'] = $company;
    }
    $navGroups = array_filter($navGroups);

    $openGroups = [];
    foreach ($navGroups as $group => $items) {
        foreach ($items as $item) {
            if (request()->routeIs($item['pattern'])) {
                $openGroups[] = $group;
            }
        }
    }
    if (request()->routeIs('admin.units.*')) {
        $openGroups[] = 'Inventory';
    }

    $crumbs = [['label' => 'Desk', 'url' => route('admin.dashboard')]];
    if (request()->routeIs('admin.dashboard')) {
        $crumbs[] = ['label' => 'Dashboard'];
    } elseif (request()->routeIs('admin.notifications.*')) {
        $crumbs[] = ['label' => 'Notifications'];
    } elseif (request()->routeIs('admin.properties.*', 'admin.units.*')) {
        $crumbs[] = ['label' => 'Inventory', 'url' => route('admin.properties.index')];
        $crumbs[] = ['label' => $pageTitle];
    } elseif (request()->routeIs('admin.projects.*')) {
        $crumbs[] = ['label' => 'Inventory', 'url' => route('admin.projects.index')];
        $crumbs[] = ['label' => $pageTitle];
    } elseif (request()->routeIs('admin.review.*')) {
        $crumbs[] = ['label' => 'Inventory', 'url' => route('admin.properties.index')];
        $crumbs[] = ['label' => 'Review queue'];
    } elseif (request()->routeIs('admin.leads.*')) {
        $crumbs[] = ['label' => 'Sales', 'url' => route('admin.leads.index')];
        $crumbs[] = ['label' => $pageTitle];
    } elseif (request()->routeIs('admin.follow-ups.*')) {
        $crumbs[] = ['label' => 'Sales', 'url' => route('admin.leads.index')];
        $crumbs[] = ['label' => 'Follow-ups'];
    } elseif (request()->routeIs('admin.visits.*')) {
        $crumbs[] = ['label' => 'Sales', 'url' => route('admin.leads.index')];
        $crumbs[] = ['label' => 'Site visits'];
    } elseif (request()->routeIs('admin.cms.*')) {
        $crumbs[] = ['label' => 'Website', 'url' => route('admin.cms.index')];
        $crumbs[] = ['label' => $pageTitle];
    } elseif (request()->routeIs('admin.posts.*')) {
        $crumbs[] = ['label' => 'Website', 'url' => route('admin.posts.index')];
        $crumbs[] = ['label' => $pageTitle];
    } elseif (request()->routeIs('admin.faqs.*')) {
        $crumbs[] = ['label' => 'Website', 'url' => route('admin.cms.index')];
        $crumbs[] = ['label' => 'FAQs'];
    } elseif (request()->routeIs('admin.menus.*')) {
        $crumbs[] = ['label' => 'Website', 'url' => route('admin.cms.index')];
        $crumbs[] = ['label' => 'Menus'];
    } elseif (request()->routeIs('admin.areas.*', 'admin.property-types.*', 'admin.amenities.*', 'admin.listing-display')) {
        $crumbs[] = ['label' => 'Catalogue', 'url' => $canReference ? route('admin.areas.index') : route('admin.listing-display')];
        $crumbs[] = ['label' => $pageTitle];
    } elseif (request()->routeIs('admin.settings.*')) {
        $crumbs[] = ['label' => 'Company', 'url' => $settingsUrl];
        $crumbs[] = ['label' => 'Settings'];
    } elseif (request()->routeIs('admin.staff.*')) {
        $crumbs[] = ['label' => 'Company', 'url' => route('admin.staff.index')];
        $crumbs[] = ['label' => $pageTitle];
    } elseif (request()->routeIs('admin.redirects.*')) {
        $crumbs[] = ['label' => 'Company', 'url' => route('admin.redirects.index')];
        $crumbs[] = ['label' => 'Redirects'];
    } elseif (request()->routeIs('admin.audit.*')) {
        $crumbs[] = ['label' => 'Company'];
        $crumbs[] = ['label' => 'Audit log'];
    } else {
        $crumbs[] = ['label' => $pageTitle];
    }

    $systemWarnings = [];
    if ($user?->isOwnerAdmin()) {
        if (! \App\Support\HealthStatus::mailConfigured()) {
            $systemWarnings[] = 'Email is not configured, so staff alerts and enquiry acknowledgements are not being sent. Set the mail settings in the server environment.';
        }
        $failedJobs = \Illuminate\Support\Facades\Cache::remember('uh:admin:failed-jobs', 300, fn (): int => \Illuminate\Support\Facades\Schema::hasTable('failed_jobs') ? \Illuminate\Support\Facades\DB::table('failed_jobs')->count() : 0);
        if ($failedJobs > 0) {
            $systemWarnings[] = trans_choice(':count background job has failed (for example a notification email). Run "php artisan queue:failed" on the server to inspect it.|:count background jobs have failed (for example notification emails). Run "php artisan queue:failed" on the server to inspect them.', $failedJobs);
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

        <div class="uh-admin-app">
            <div class="uh-admin-backdrop" x-show="mobile" x-cloak @click="closeMobile()" aria-hidden="true"></div>

            <aside id="admin-nav" class="uh-admin-sidebar" :class="{ 'is-open': mobile }" aria-label="Staff navigation">
                <div class="uh-admin-brand">
                    <a href="{{ route('admin.dashboard') }}" class="uh-admin-brand-link">
                        <span class="uh-admin-brand-mark" aria-hidden="true">UH</span>
                        <span class="uh-admin-brand-copy">
                            <span class="uh-admin-brand-name">Urban Haven</span>
                            <span class="uh-admin-brand-tag">Operations</span>
                        </span>
                    </a>
                    <button type="button" class="uh-admin-icon-btn uh-admin-sidebar-close" @click="closeMobile()" aria-label="Close navigation">
                        <x-icon name="close" class="size-5" />
                    </button>
                </div>

                <nav class="uh-admin-nav">
                    @foreach($navGroups as $group => $items)
                        @continue($items === [])
                        <div class="uh-admin-group">
                            <button type="button" class="uh-admin-group-toggle" @click="toggleGroup(@js($group))"
                                    :aria-expanded="groupOpen(@js($group)).toString()">
                                <span class="uh-admin-group-label">{{ $group }}</span>
                                <x-icon name="chevron-down" class="uh-admin-group-chevron size-3.5" />
                            </button>
                            <ul class="uh-admin-menu" x-show="collapsed || groupOpen(@js($group))">
                                @foreach($items as $item)
                                    @php($current = request()->routeIs($item['pattern']))
                                    <li>
                                        <a href="{{ $item['url'] }}"
                                           title="{{ $item['label'] }}"
                                           @if($current) aria-current="page" @endif
                                           @class(['uh-admin-link', 'is-active' => $current])>
                                            <x-icon :name="$item['icon']" class="uh-admin-link-icon" />
                                            <span class="uh-admin-link-label">{{ $item['label'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </nav>

                <div class="uh-admin-sidebar-foot">
                    <button type="button" class="uh-admin-collapse-btn" @click="toggleCollapsed()" aria-label="Collapse sidebar">
                        <x-icon name="chevron-left" class="size-4" />
                        <span class="uh-admin-link-label">Collapse</span>
                    </button>
                    <a class="uh-admin-link" href="{{ route('home') }}" title="View public site">
                        <x-icon name="external" class="uh-admin-link-icon" />
                        <span class="uh-admin-link-label">View public site</span>
                    </a>
                </div>
            </aside>

            <div class="uh-admin-shell">
                <header class="uh-admin-header">
                    <div class="uh-admin-header-start">
                        <button type="button" class="uh-admin-icon-btn uh-admin-mobile-toggle" @click="openMobile()"
                                :aria-expanded="mobile.toString()" aria-controls="admin-nav" aria-label="Open navigation">
                            <x-icon name="menu" class="size-5" />
                        </button>
                        <button type="button" class="uh-admin-icon-btn uh-admin-desktop-toggle" @click="toggleCollapsed()"
                                aria-label="Toggle sidebar">
                            <x-icon name="menu" class="size-5" />
                        </button>
                        <x-ui.breadcrumbs :items="$crumbs" />
                    </div>

                    <div class="uh-admin-header-end">
                        @include('admin.notifications._bell')

                        <div class="uh-admin-user" x-data="{ open: false }" @keydown.escape="open = false">
                            <button type="button" class="uh-admin-user-btn" @click="open = ! open" :aria-expanded="open.toString()">
                                <span class="uh-admin-avatar">{{ $initials ?: 'S' }}</span>
                                <span class="uh-admin-user-meta">
                                    <span class="uh-admin-user-name">{{ $user->name }}</span>
                                    <span class="uh-admin-user-role">{{ $user->roles->pluck('label')->join(', ') ?: 'Staff' }}</span>
                                </span>
                                <x-icon name="chevron-down" class="size-3.5 opacity-50" />
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

                <main id="main" class="uh-admin-content">
                    <div class="uh-admin-workspace">
                        @foreach($systemWarnings as $warning)
                            <x-ui.alert tone="warn" class="mb-4">{{ $warning }}</x-ui.alert>
                        @endforeach
                        <x-ui.flash />
                        @yield('content')
                    </div>
                </main>
            </div>
        </div>
        @include('admin.partials.quick-add')
    </body>
</html>
