@extends('layouts.admin')
@section('title', 'Menu management')

@php
    $parentOptions = $parents->map(fn ($items) => $items->map(fn ($item) => ['id' => $item->id, 'label' => $item->label])->values())->all();
@endphp

@section('content')
    <x-ui.page-header compact title="Menu management">
        <x-slot:eyebrow>Content library</x-slot:eyebrow>
    </x-ui.page-header>

    <x-ui.admin-related label="Also on the site">
        <a href="{{ route('admin.cms.index') }}">Pages</a>
        <a href="{{ route('admin.home-sections.index') }}">Home page</a>
        @can('redirect.manage')
            <a href="{{ route('admin.redirects.index') }}">Redirects</a>
        @endcan
    </x-ui.admin-related>

    <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <div class="space-y-6">
            @foreach($locations as $location => $label)
                @php
                    $links = $menus->get($location, collect());
                @endphp
                <section class="uh-panel" aria-labelledby="menu-{{ $location }}">
                    <div class="flex items-center justify-between gap-3">
                        <h2 id="menu-{{ $location }}" class="uh-h4">{{ $label }}</h2>
                        <span class="text-xs text-[var(--color-muted)]">{{ $links->count() }} {{ \Illuminate\Support\Str::plural('link', $links->count()) }}</span>
                    </div>
                    @if($links->isNotEmpty())
                        <ol class="dd-menu-list mt-3">
                            @foreach($links as $item)
                                @include('admin.menus._item', ['item' => $item, 'siblings' => $links, 'depth' => 0])
                                @if($item->children->isNotEmpty())
                                    <li>
                                        <ol class="dd-menu-children">
                                            @foreach($item->children as $child)
                                                @include('admin.menus._item', ['item' => $child, 'siblings' => $item->children, 'depth' => 1])
                                            @endforeach
                                        </ol>
                                    </li>
                                @endif
                            @endforeach
                        </ol>
                    @else
                        <p class="mt-3 text-sm text-[var(--color-muted)]">Using the built-in links.</p>
                    @endif
                </section>
            @endforeach
        </div>

        <form method="POST" action="{{ route('admin.menus.store') }}" class="uh-panel space-y-3 lg:sticky lg:top-20"
              x-data="{ location: @js(old('location', $prefillLocation)), parents: @js($parentOptions) }">
            @csrf
            <h2 class="uh-h4">Add a link</h2>
            <x-ui.select name="location" label="Menu" x-model="location">
                @foreach($locations as $location => $label)
                    <option value="{{ $location }}">{{ $label }}</option>
                @endforeach
            </x-ui.select>
            <div class="uh-field">
                <label class="uh-label" for="new-parent">Sits under</label>
                <select id="new-parent" name="parent_id" class="uh-select" data-native-select>
                    <option value="">Top level</option>
                    <template x-for="parent in (parents[location] || [])" :key="parent.id">
                        <option :value="parent.id" x-text="parent.label"></option>
                    </template>
                </select>
                @error('parent_id')<p class="uh-error">{{ $message }}</p>@enderror
            </div>
            <x-ui.input name="label" label="Label" :value="$prefillLabel" required maxlength="60" />
            <x-ui.input name="url" label="Link" :value="$prefillUrl" required maxlength="255" dir="ltr" placeholder="/properties?listing_type=sale" hint="An internal path starting with / or a full https:// address." />
            <label class="uh-check"><input type="checkbox" name="opens_new_tab" value="1"> <span>Open in a new tab</span></label>
            <input type="hidden" name="is_visible" value="1">
            <button type="submit" class="uh-btn-primary uh-btn-sm uh-btn-block">Add link</button>
        </form>
    </div>
@endsection
