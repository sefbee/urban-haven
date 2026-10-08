@extends('layouts.admin')
@section('title', 'Pages & slugs')

@section('content')
    <x-ui.page-header compact title="Pages & slugs">
        <x-slot:eyebrow>Content library</x-slot:eyebrow>
        <x-slot:actions>
            @if($canManageMenus)
                <a class="uh-btn-outline uh-btn-sm" href="{{ route('admin.menus.index') }}">{{ __('Manage menus') }}</a>
            @endif
            @if($canCreatePages)
                <a class="uh-btn-primary uh-btn-sm" href="{{ route('admin.cms.create') }}">
                    <x-icon name="plus" class="size-4" /> {{ __('New page') }}
                </a>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <div x-data="{
        q: '',
        group: 'all',
        copied: null,
        copyError: false,
        showMenuModal: false,
        menu: { label: '', url: '', location: @js(array_key_first($menuLocations)), parent_id: '' },
        menuParents: @js($menuParents),
        openMenu(label, url) {
            this.menu = { label, url, location: @js(array_key_first($menuLocations)), parent_id: '' };
            this.showMenuModal = true;
            this.$nextTick(() => this.$refs.menuLocation.focus());
        },
        async copy(url) {
            this.copyError = false;
            this.copied = null;
            try {
                let didCopy = false;
                if (navigator.clipboard?.writeText && window.isSecureContext) {
                    try {
                        await navigator.clipboard.writeText(url);
                        didCopy = true;
                    } catch {
                        didCopy = false;
                    }
                }
                if (!didCopy) {
                    const field = document.createElement('textarea');
                    field.value = url;
                    field.setAttribute('readonly', '');
                    field.style.position = 'fixed';
                    field.style.opacity = '0';
                    document.body.append(field);
                    field.select();
                    try {
                        didCopy = document.execCommand('copy');
                    } finally {
                        field.remove();
                    }
                }
                if (!didCopy) {
                    throw new Error('Clipboard unavailable');
                }
                this.copied = url;
                window.setTimeout(() => this.copied = null, 1500);
            } catch {
                this.copyError = true;
            }
        }
    }">
        <div class="dd-urls-feedback" role="status" aria-live="polite" x-show="copyError || copied" x-cloak
             x-text="copyError ? @js(__('Could not copy the URL. Select and copy it from the address column.')) : @js(__('URL copied to clipboard'))"></div>
        <div class="dd-urls-toolbar">
            <label class="dd-urls-search">
                <x-icon name="search" class="size-4 text-[var(--color-muted)]" />
                <span class="sr-only">Filter addresses</span>
                <input type="search" x-model="q" placeholder="Filter by name or address…">
            </label>
            <div class="dd-urls-tabs" role="tablist">
                <button type="button" :class="group === 'all' && 'is-active'" @click="group = 'all'">All</button>
                @foreach($groups as $name => $rows)
                    <button type="button" :class="group === @js($name) && 'is-active'" @click="group = @js($name)">{{ $name }} <span>{{ count($rows) }}</span></button>
                @endforeach
            </div>
        </div>

        @foreach($groups as $name => $rows)
            <section class="uh-panel-flush mt-4 overflow-hidden" x-show="group === 'all' || group === @js($name)">
                <h2 class="border-b border-[var(--color-line)] px-4 py-3 text-sm font-semibold">{{ $name }}</h2>
                <div class="uh-table-scroll">
                    <table class="uh-table">
                        <caption class="sr-only">{{ $name }}</caption>
                        <thead>
                            <tr>
                                <th scope="col">Name</th>
                                <th scope="col">Type</th>
                                <th scope="col">Address</th>
                                <th scope="col">Status</th>
                                <th scope="col">SEO</th>
                                <th scope="col"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                                @php
                                    $url = url($row['path']);
                                    $haystack = Str::lower($row['label'].' '.$row['path']);
                                @endphp
                                <tr x-show="q === '' || @js($haystack).includes(q.toLowerCase())">
                                    <td class="min-w-48 font-medium">{{ $row['label'] }}</td>
                                    <td class="text-xs text-[var(--color-muted)]">{{ $row['type'] }}</td>
                                    <td class="text-xs" dir="ltr">
                                        @if($row['live'])
                                            <a class="uh-link" href="{{ $url }}" target="_blank" rel="noopener">{{ $row['path'] }}</a>
                                        @else
                                            <span class="text-[var(--color-muted)]">{{ $row['path'] }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <x-ui.badge :tone="$row['live'] ? 'success' : 'outline'">{{ $row['status'] ?? ($row['live'] ? 'Live' : 'Not live') }}</x-ui.badge>
                                    </td>
                                    <td>
                                        <x-ui.badge :tone="$row['seo'] ? 'success' : 'neutral'">{{ $row['seo'] ? 'Custom' : 'Default' }}</x-ui.badge>
                                    </td>
                                    <td class="uh-admin-row-actions">
                                        <button type="button" class="uh-btn-ghost uh-btn-sm" @click="copy(@js($url))">
                                            <x-icon name="copy" class="size-3.5" />
                                            <span x-text="copied === @js($url) ? @js(__('Copied')) : @js(__('Copy URL'))">{{ __('Copy URL') }}</span>
                                        </button>
                                        @if($canManageMenus)
                                            <button type="button" class="uh-btn-ghost uh-btn-sm" @click="openMenu(@js($row['label']), @js($row['path']))">{{ __('Add to menu') }}</button>
                                        @endif
                                        <a class="uh-btn-ghost uh-btn-sm" href="{{ $row['edit'] }}">{{ __('Edit') }}</a>
                                        @if(! empty($row['delete']))
                                            <form method="POST" action="{{ $row['delete'] }}" x-data="uhConfirm(@js(__('Delete this page? This cannot be undone.')))" @submit="confirm($event)">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="uh-btn-ghost uh-btn-sm text-[var(--color-danger)]">{{ __('Delete') }}</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endforeach

        @if($canManageMenus)
            <div class="dd-urls-modal-backdrop" x-show="showMenuModal" x-transition.opacity x-cloak
                 @click.self="showMenuModal = false" @keydown.escape.window="showMenuModal = false">
                <section class="dd-urls-modal" role="dialog" aria-modal="true" aria-labelledby="add-menu-title">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 id="add-menu-title" class="uh-h4">{{ __('Add page to menu') }}</h2>
                        </div>
                        <button type="button" class="uh-btn-ghost uh-btn-sm" aria-label="{{ __('Close') }}" @click="showMenuModal = false">×</button>
                    </div>
                    <form method="POST" action="{{ route('admin.menus.store') }}" class="mt-5 space-y-4">
                        @csrf
                        <div class="uh-field">
                            <label class="uh-label" for="menu-location">{{ __('Menu') }}</label>
                            <select id="menu-location" x-ref="menuLocation" name="location" class="uh-select" x-model="menu.location" @change="menu.parent_id = ''" required>
                                @foreach($menuLocations as $location => $label)
                                    <option value="{{ $location }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="uh-field">
                            <label class="uh-label" for="menu-parent">{{ __('Parent link') }}</label>
                            <select id="menu-parent" name="parent_id" class="uh-select" x-model="menu.parent_id">
                                <option value="">{{ __('Top level') }}</option>
                                <template x-for="parent in menuParents.filter(item => item.location === menu.location)" :key="parent.id">
                                    <option :value="parent.id" x-text="parent.label"></option>
                                </template>
                            </select>
                        </div>
                        <div class="uh-field">
                            <label class="uh-label" for="menu-label">{{ __('Label') }}</label>
                            <input id="menu-label" name="label" class="uh-input" x-model="menu.label" maxlength="60" required>
                        </div>
                        <div class="uh-field">
                            <label class="uh-label" for="menu-url">{{ __('Link') }}</label>
                            <input id="menu-url" name="url" class="uh-input" x-model="menu.url" dir="ltr" maxlength="255" required>
                        </div>
                        <input type="hidden" name="is_visible" value="1">
                        <div class="flex flex-wrap justify-end gap-2">
                            <button type="button" class="uh-btn-outline uh-btn-sm" @click="showMenuModal = false">{{ __('Cancel') }}</button>
                            <button type="submit" class="uh-btn-primary uh-btn-sm">{{ __('Add link') }}</button>
                        </div>
                    </form>
                </section>
            </div>
        @endif
    </div>
@endsection
