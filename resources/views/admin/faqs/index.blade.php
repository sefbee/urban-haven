@extends('layouts.admin')
@section('title', 'FAQs')

@php
    $drawer = $errors->any() ? old('_drawer', 'create') : null;
@endphp

@section('content')
    <div x-data="uhAdminDrawers(@js($drawer))">
        <x-ui.page-header compact title="Frequently asked questions">
            <x-slot:eyebrow>Website pages</x-slot:eyebrow>
            <x-slot:actions>
                <button type="button" class="uh-btn-primary uh-btn-sm" @click="open('create')">
                    <x-icon name="plus" class="size-4" />
                    Add question
                </button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.admin-related label="Also on the site">
            <a href="{{ route('admin.cms.index') }}">Custom pages</a>
            <a href="{{ route('admin.posts.index') }}">Articles</a>
        </x-ui.admin-related>

        @forelse($faqs->groupBy('group') as $group => $items)
            <section @class(['uh-panel', 'mt-4' => ! $loop->first])>
                <h2 class="uh-h4">{{ $group }}</h2>
                <ul class="mt-3 divide-y divide-[var(--color-line)]">
                    @foreach($items as $faq)
                        <li id="faq-{{ $faq->id }}" class="flex scroll-mt-24 flex-wrap items-start justify-between gap-3 py-3 first:pt-1 last:pb-0">
                            <p class="min-w-0 flex-1 font-medium">{{ $faq->question }}</p>
                            <span class="flex items-center gap-2">
                                <x-ui.badge :tone="$faq->is_visible ? 'success' : 'neutral'">{{ $faq->is_visible ? 'Visible' : 'Hidden' }}</x-ui.badge>
                                <button type="button" class="uh-btn-ghost uh-btn-sm" @click="open('edit-{{ $faq->id }}')">Edit</button>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </section>
        @empty
            <x-ui.empty icon="info" title="No questions yet" description="Add the questions buyers and tenants ask your sales team most often.">
                <button type="button" class="uh-btn-primary uh-btn-sm" @click="open('create')">Add question</button>
            </x-ui.empty>
        @endforelse

        <datalist id="faq-groups">
            @foreach($faqs->pluck('group')->filter()->unique()->sort() as $existingGroup)
                <option value="{{ $existingGroup }}"></option>
            @endforeach
        </datalist>

        <x-ui.admin-drawer name="create" title="Add a question">
            <form method="POST" action="{{ route('admin.faqs.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="_drawer" value="create">
                <x-ui.input name="group" label="Topic" maxlength="80" optional list="faq-groups"
                            hint="Questions are grouped by topic on the FAQ page. Pick one or type a new topic." />
                <x-ui.input name="question" label="Question" required maxlength="255" />
                <x-ui.rich-text name="answer" label="Answer" min-height="8rem" placeholder="Answer in two or three short sentences…" />
                <x-ui.input name="sort_order" label="Order within the topic" type="number" min="0" max="999" optional hint="Lower numbers show first." />
                @if($canPublish)
                    <input type="hidden" name="is_visible" value="0">
                    <label class="uh-check"><input type="checkbox" name="is_visible" value="1" @checked(old('is_visible', '1'))> <span>Visible on the site</span></label>
                @endif
                <button type="submit" class="uh-btn-primary uh-btn-block">Add question</button>
            </form>
        </x-ui.admin-drawer>

        @foreach($faqs as $faq)
            <x-ui.admin-drawer :name="'edit-'.$faq->id" :title="'Edit question'">
                <form method="POST" action="{{ route('admin.faqs.update', $faq) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_drawer" value="edit-{{ $faq->id }}">
                    <x-ui.input name="group" label="Topic" :value="$faq->group" maxlength="80" list="faq-groups" :id="'g-'.$faq->id" />
                    <x-ui.input name="question" label="Question" :value="$faq->question" required maxlength="255" :id="'q-'.$faq->id" />
                    <x-ui.rich-text name="answer" label="Answer" :value="$faq->answer" min-height="8rem" :id="'a-'.$faq->id" />
                    <x-ui.input name="sort_order" label="Order within the topic" type="number" min="0" max="999" :value="$faq->sort_order" :id="'o-'.$faq->id" />
                    @if($canPublish)
                        <input type="hidden" name="is_visible" value="0">
                        <label class="uh-check"><input type="checkbox" name="is_visible" value="1" @checked($faq->is_visible)> <span>Visible on the site</span></label>
                    @endif
                    <button type="submit" class="uh-btn-primary uh-btn-block">Save</button>
                </form>
                <form method="POST" action="{{ route('admin.faqs.destroy', $faq) }}" class="mt-4 border-t border-[var(--color-line)] pt-4" x-data="uhConfirm('Delete this question?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="uh-btn-ghost uh-btn-sm px-0 text-[var(--color-danger)]" @click="confirm($event)">Delete</button>
                </form>
            </x-ui.admin-drawer>
        @endforeach
    </div>
@endsection
