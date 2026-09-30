@php
    $user = auth()->user();
    $navGroups = [
        'Overview' => [
            ['pattern' => 'admin.dashboard', 'label' => 'Dashboard', 'url' => route('admin.dashboard'), 'icon' => 'dashboard'],
            ['pattern' => 'admin.notifications.*', 'label' => 'Notifications', 'url' => route('admin.notifications.index'), 'icon' => 'bell'],
        ],
        'Inventory' => [
            ['pattern' => 'admin.properties.*', 'label' => 'Properties', 'url' => route('admin.properties.index'), 'icon' => 'home'],
            ['pattern' => 'admin.projects.*', 'label' => 'Projects', 'url' => route('admin.projects.index'), 'icon' => 'building'],
        ],
        'Sales' => [
            ['pattern' => 'admin.leads.*', 'label' => 'Leads', 'url' => route('admin.leads.index'), 'icon' => 'inbox'],
            ['pattern' => 'admin.visits.*', 'label' => 'Site visits', 'url' => route('admin.visits.index'), 'icon' => 'calendar'],
        ],
        'Content' => [
            ['pattern' => 'admin.cms.*', 'label' => 'Pages', 'url' => route('admin.cms.index'), 'icon' => 'document'],
        ],
    ];

    $administration = [];
    if ($user?->can('viewAny', App\Models\User::class)) {
        $administration[] = ['pattern' => 'admin.staff.*', 'label' => 'Staff', 'url' => route('admin.staff.index'), 'icon' => 'users'];
    }
    if ($user?->isOwnerAdmin()) {
        $administration[] = ['pattern' => 'admin.settings.*', 'label' => 'Settings', 'url' => route('admin.settings.index'), 'icon' => 'settings'];
    }
    if ($administration !== []) {
        $navGroups['Administration'] = $administration;
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">
        <title>@yield('title', 'Staff desk') — Urban Haven</title>
        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-sand text-ink antialiased" x-data="{ sidebar: false }" @keydown.escape="sidebar = false">
        <a class="uh-skip" href="#main">Skip to content</a>

        <div class="flex min-h-screen">
            {{-- Mobile backdrop --}}
            <div x-show="sidebar" x-cloak class="fixed inset-0 z-30 bg-ink/50 md:hidden"
                 @click="sidebar = false" aria-hidden="true"></div>

            <aside id="admin-nav"
                   class="fixed inset-y-0 left-0 z-40 flex w-64 shrink-0 -translate-x-full flex-col bg-ink text-cream transition-transform duration-200 md:static md:translate-x-0"
                   :class="sidebar && 'translate-x-0'"
                   aria-label="Staff navigation">
                <div class="flex items-center justify-between px-5 py-5">
                    <a href="{{ route('admin.dashboard') }}" class="block">
                        <span class="font-display text-lg tracking-tight text-cream">Urban Haven</span>
                        <span class="mt-0.5 block text-[0.6875rem] uppercase tracking-[0.18em] text-gold-soft">Staff desk</span>
                    </a>
                    <button type="button" class="uh-icon-btn text-cream hover:bg-white/10 md:hidden"
                            @click="sidebar = false" aria-label="Close navigation">
                        <x-icon name="close" class="size-5" />
                    </button>
                </div>

                <nav class="flex-1 overflow-y-auto px-3 pb-6">
                    @foreach($navGroups as $group => $items)
                        <p class="px-3 pb-1.5 pt-4 text-[0.625rem] font-semibold uppercase tracking-[0.16em] text-cream/40">{{ $group }}</p>
                        <ul class="space-y-0.5">
                            @foreach($items as $item)
                                @php($current = request()->routeIs($item['pattern']))
                                <li>
                                    <a href="{{ $item['url'] }}"
                                       @if($current) aria-current="page" @endif
                                       @class([
                                           'flex min-h-10 items-center gap-2.5 rounded-lg px-3 text-sm transition',
                                           'bg-white/12 font-semibold text-gold-soft' => $current,
                                           'text-cream/75 hover:bg-white/8 hover:text-cream' => ! $current,
                                       ])>
                                        <x-icon :name="$item['icon']" class="size-4 shrink-0" />
                                        {{ $item['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endforeach
                </nav>

                <div class="border-t border-white/10 px-5 py-4">
                    <a class="inline-flex items-center gap-1.5 text-xs text-cream/60 transition hover:text-cream" href="{{ route('home') }}">
                        <x-icon name="external" class="size-3.5" />
                        View public site
                    </a>
                </div>
            </aside>

            <div class="flex min-w-0 flex-1 flex-col">
                <header class="sticky top-0 z-20 flex h-14 items-center justify-between gap-4 border-b border-line bg-paper px-4 md:px-7">
                    <div class="flex min-w-0 items-center gap-3">
                        <button type="button" class="uh-icon-btn md:hidden" @click="sidebar = true"
                                :aria-expanded="sidebar.toString()" aria-controls="admin-nav" aria-label="Open navigation">
                            <x-icon name="menu" class="size-5" />
                        </button>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold leading-tight">{{ $user->name }}</p>
                            <p class="truncate text-[0.6875rem] uppercase tracking-[0.1em] text-[var(--color-muted)]">
                                {{ $user->roles->pluck('label')->join(', ') ?: 'Staff' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5">
                        @include('admin.notifications._bell')
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="uh-btn-ghost uh-btn-sm">
                                <x-icon name="logout" class="size-4" />
                                <span class="hidden sm:inline">Sign out</span>
                            </button>
                        </form>
                    </div>
                </header>

                <main id="main" class="flex-1 px-4 py-6 md:px-7 md:py-8">
                    <x-ui.flash />
                    @yield('content')
                </main>
            </div>
        </div>
    </body>
</html>
