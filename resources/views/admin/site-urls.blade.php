@extends('layouts.admin')
@section('title', 'Pages & slugs')

@section('content')
    <x-ui.page-header compact title="Pages & slugs"
                      description="Every public address on the website. Copy one for a menu, an advert or a WhatsApp message, and see which pages still use default search engine text.">
        <x-slot:eyebrow>Content library</x-slot:eyebrow>
    </x-ui.page-header>

    <div x-data="{ q: '', group: 'all', copied: null, copy(url) { navigator.clipboard?.writeText(url).then(() => { this.copied = url; setTimeout(() => this.copied = null, 1500); }); } }">
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
                                    <td class="text-xs" dir="ltr">
                                        @if($row['live'])
                                            <a class="uh-link" href="{{ $url }}" target="_blank" rel="noopener">{{ $row['path'] }}</a>
                                        @else
                                            <span class="text-[var(--color-muted)]">{{ $row['path'] }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <x-ui.badge :tone="$row['live'] ? 'success' : 'outline'">{{ $row['live'] ? 'Live' : 'Not live' }}</x-ui.badge>
                                    </td>
                                    <td>
                                        <x-ui.badge :tone="$row['seo'] ? 'success' : 'neutral'">{{ $row['seo'] ? 'Custom' : 'Default' }}</x-ui.badge>
                                    </td>
                                    <td class="uh-admin-row-actions">
                                        <button type="button" class="uh-btn-ghost uh-btn-sm" @click="copy(@js($url))">
                                            <x-icon name="copy" class="size-3.5" />
                                            <span x-text="copied === @js($url) ? 'Copied' : 'Copy URL'">Copy URL</span>
                                        </button>
                                        <a class="uh-btn-ghost uh-btn-sm" href="{{ $row['edit'] }}">Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endforeach
    </div>
@endsection
