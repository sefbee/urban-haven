@extends('layouts.admin')
@section('title', 'FAQs')

@php
    $drawer = $errors->any() ? old('_drawer', 'create') : null;
@endphp

@section('content')
    <div x-data="uhAdminDrawers(@js($drawer))">
        <x-ui.page-header compact title="Frequently asked questions"
                          :description="$canPublish ? 'Visible questions appear on the public FAQ page and in search results as structured data.' : 'New questions stay hidden until a publisher makes them visible.'">
            <x-slot:eyebrow>Website</x-slot:eyebrow>
            <x-slot:actions>
                <button type="button" class="uh-btn-primary uh-btn-sm" @click="open('create')">
                    <x-icon name="plus" class="size-4" />
                    Add question
                </button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.admin-related label="Also on the site">
            <a href="{{ route('admin.cms.index') }}">Pages</a>
            <a href="{{ route('admin.posts.index') }}">Articles</a>
        </x-ui.admin-related>

        @forelse($faqs->groupBy('group') as $group => $items)
            <section @class(['uh-panel', 'mt-4' => ! $loop->first])>
                <h2 class="uh-h4">{{ $group }}</h2>
                <ul class="mt-3 divide-y divide-[var(--color-line)]">
                    @foreach($items as $faq)
                        <li class="flex flex-wrap items-start justify-between gap-3 py-3 first:pt-1 last:pb-0">
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

        <x-ui.admin-drawer name="create" title="Add a question">
            <form method="POST" action="{{ route('admin.faqs.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="_drawer" value="create">
                <x-ui.input name="question" label="Question" required maxlength="255" />
                <x-ui.textarea name="answer" label="Answer" rows="4" required />
                <x-ui.input name="group" label="Group" maxlength="80" optional placeholder="Buying" />
                <x-ui.input name="sort_order" label="Order" type="number" min="0" max="999" optional />
                @if($canPublish)
                    <input type="hidden" name="is_visible" value="0">
                    <label class="uh-check"><input type="checkbox" name="is_visible" value="1"> <span>Visible on the site</span></label>
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
                    <x-ui.input name="question" label="Question" :value="$faq->question" required maxlength="255" :id="'q-'.$faq->id" />
                    <x-ui.textarea name="answer" label="Answer" rows="4" :value="$faq->answer" required :id="'a-'.$faq->id" />
                    <div class="grid gap-3 sm:grid-cols-2">
                        <x-ui.input name="group" label="Group" :value="$faq->group" maxlength="80" :id="'g-'.$faq->id" />
                        <x-ui.input name="sort_order" label="Order" type="number" min="0" max="999" :value="$faq->sort_order" :id="'o-'.$faq->id" />
                    </div>
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
