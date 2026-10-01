@extends('layouts.admin')
@section('title', 'FAQs')

@section('content')
    <x-ui.page-header compact title="Frequently asked questions"
                      :description="$canPublish ? 'Visible questions appear on the public FAQ page and in search results as structured data.' : 'New questions stay hidden until a publisher makes them visible.'" />

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            @forelse($faqs->groupBy('group') as $group => $items)
                <section class="uh-panel">
                    <h2 class="uh-h4">{{ $group }}</h2>
                    <ul class="mt-3 space-y-3">
                        @foreach($items as $faq)
                            <li class="rounded-lg border border-line p-3" x-data="{ editing: false }">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <p class="font-medium">{{ $faq->question }}</p>
                                    <span class="flex items-center gap-2">
                                        <x-ui.badge :tone="$faq->is_visible ? 'success' : 'neutral'">{{ $faq->is_visible ? 'Visible' : 'Hidden' }}</x-ui.badge>
                                        <button type="button" class="uh-btn-ghost uh-btn-sm" @click="editing = ! editing" :aria-expanded="editing">Edit</button>
                                    </span>
                                </div>
                                <form method="POST" action="{{ route('admin.faqs.update', $faq) }}" class="mt-3 space-y-3" x-show="editing" x-cloak>
                                    @csrf
                                    @method('PUT')
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
                                    <button type="submit" class="uh-btn-primary uh-btn-sm">Save</button>
                                </form>
                                <form method="POST" action="{{ route('admin.faqs.destroy', $faq) }}" class="mt-2" x-show="editing" x-cloak x-data="uhConfirm('Delete this question?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="uh-btn-ghost uh-btn-sm px-0 text-[var(--color-danger)]" @click="confirm($event)">Delete</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @empty
                <x-ui.empty icon="info" title="No questions yet" description="Add the questions buyers and tenants ask your sales team most often." />
            @endforelse
        </div>

        <form method="POST" action="{{ route('admin.faqs.store') }}" class="uh-panel h-fit space-y-3">
            @csrf
            <h2 class="uh-h4">Add a question</h2>
            <x-ui.input name="question" label="Question" required maxlength="255" />
            <x-ui.textarea name="answer" label="Answer" rows="4" required />
            <x-ui.input name="group" label="Group" maxlength="80" optional placeholder="Buying" />
            <x-ui.input name="sort_order" label="Order" type="number" min="0" max="999" optional />
            @if($canPublish)
                <input type="hidden" name="is_visible" value="0">
                <label class="uh-check"><input type="checkbox" name="is_visible" value="1"> <span>Visible on the site</span></label>
            @endif
            <button type="submit" class="uh-btn-primary uh-btn-sm uh-btn-block">Add question</button>
        </form>
    </div>
@endsection
