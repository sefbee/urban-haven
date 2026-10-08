@extends('layouts.admin')
@section('title', $term !== '' ? 'Search: '.$term : 'Search')

@section('content')
    <x-ui.page-header compact title="Search"
                      description="Find any property, project, lead, article, page, FAQ or staff member you have access to.">
        <x-slot:eyebrow>Dashboard</x-slot:eyebrow>
    </x-ui.page-header>

    <form method="GET" action="{{ route('admin.search') }}" class="dd-search-hero" role="search">
        <x-icon name="search" class="dd-search-hero-icon" />
        <label for="search-page-q" class="sr-only">Search term</label>
        <input id="search-page-q" type="search" name="q" value="{{ $term }}" placeholder="Name, reference, phone, email or title" maxlength="120" autofocus autocomplete="off">
        <button type="submit" class="uh-btn-primary">Search</button>
    </form>

    @if($term === '')
        <p class="mt-6 text-sm text-[var(--color-muted)]">Tip: press <kbd class="dd-kbd">/</kbd> anywhere in the admin to jump to the search box.</p>
    @elseif(mb_strlen($term) < 2)
        <x-ui.alert tone="info" class="mt-6">Type at least two characters.</x-ui.alert>
    @elseif($groups === [])
        <x-ui.empty icon="search" class="mt-6" title="Nothing matches “{{ $term }}”"
                    description="Check the spelling, or search by reference number, phone or email instead." />
    @else
        <p class="mt-6 text-sm text-[var(--color-muted)]">{{ $total }} {{ \Illuminate\Support\Str::plural('result', $total) }} for “<strong class="text-[var(--dd-ink)]">{{ $term }}</strong>”</p>
        <div class="dd-search-groups mt-3">
            @foreach($groups as $group => $results)
                <section class="uh-panel" aria-labelledby="search-{{ \Illuminate\Support\Str::slug($group) }}">
                    <h2 id="search-{{ \Illuminate\Support\Str::slug($group) }}" class="flex items-center justify-between uh-h4">
                        {{ $group }}
                        <span class="uh-badge">{{ count($results) }}</span>
                    </h2>
                    <ul class="dd-search-list mt-2">
                        @foreach($results as $result)
                            <li>
                                <a href="{{ $result['url'] }}" class="dd-search-result">
                                    <span class="dd-search-result-icon" aria-hidden="true"><x-icon :name="$result['icon']" class="size-4" /></span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate font-medium">{{ $result['title'] }}</span>
                                        @if($result['meta'])
                                            <span class="block truncate text-xs text-[var(--color-muted)]">{{ $result['meta'] }}</span>
                                        @endif
                                    </span>
                                    <x-icon name="chevron-right" class="size-4 text-[var(--dd-faint)]" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>
    @endif
@endsection
