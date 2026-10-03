@extends('layouts.admin')
@section('title', 'Menus')

@section('content')
    <x-ui.page-header compact title="Menus" description="Links in the public header and footer. When a menu has no visible links the site uses its built-in navigation.">
        <x-slot:eyebrow>Website</x-slot:eyebrow>
    </x-ui.page-header>

    <x-ui.admin-related label="Also on the site">
        <a href="{{ route('admin.cms.index') }}">Pages</a>
        @can('redirect.manage')
            <a href="{{ route('admin.redirects.index') }}">Redirects</a>
        @endcan
    </x-ui.admin-related>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            @foreach($locations as $location => $label)
                <section class="uh-panel" aria-labelledby="menu-{{ $location }}">
                    <h2 id="menu-{{ $location }}" class="uh-h4">{{ $label }}</h2>
                    @php($links = $items->get($location, collect()))
                    @if($links->isNotEmpty())
                        <ul class="mt-3 divide-y divide-[var(--color-line)]">
                            @foreach($links as $item)
                                <li class="py-3">
                                    <form method="POST" action="{{ route('admin.menus.update', $item) }}" class="grid items-end gap-3 sm:grid-cols-[1fr_1.4fr_5rem_auto_auto]">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="location" value="{{ $location }}">
                                        <x-ui.input name="label" label="Label" :value="$item->label" required maxlength="60" :id="'ml-'.$item->id" />
                                        <x-ui.input name="url" label="Link" :value="$item->url" required maxlength="255" dir="ltr" :id="'mu-'.$item->id" />
                                        <x-ui.input name="sort_order" label="Order" type="number" min="0" max="999" :value="$item->sort_order" :id="'mo-'.$item->id" />
                                        <div class="uh-field">
                                            <input type="hidden" name="is_visible" value="0">
                                            <label class="uh-check min-h-10"><input type="checkbox" name="is_visible" value="1" @checked($item->is_visible)> <span>Visible</span></label>
                                        </div>
                                        <button type="submit" class="uh-btn-outline uh-btn-sm">Save</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.menus.destroy', $item) }}" class="mt-1" x-data="uhConfirm('Remove this link?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="uh-btn-ghost uh-btn-sm px-0 text-[var(--color-danger)]" @click="confirm($event)">Remove</button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-3 text-sm text-[var(--color-muted)]">Using the built-in links.</p>
                    @endif
                </section>
            @endforeach
        </div>

        <form method="POST" action="{{ route('admin.menus.store') }}" class="uh-panel h-fit space-y-3">
            @csrf
            <h2 class="uh-h4">Add a link</h2>
            <x-ui.select name="location" label="Menu">
                @foreach($locations as $location => $label)
                    <option value="{{ $location }}">{{ $label }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.input name="label" label="Label" required maxlength="60" />
            <x-ui.input name="url" label="Link" required maxlength="255" dir="ltr" placeholder="/properties?listing_type=sale" hint="An internal path starting with / or a full https:// address." />
            <x-ui.input name="sort_order" label="Order" type="number" min="0" max="999" optional />
            <input type="hidden" name="is_visible" value="1">
            <button type="submit" class="uh-btn-primary uh-btn-sm uh-btn-block">Add link</button>
        </form>
    </div>
@endsection
