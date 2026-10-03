@extends('layouts.public')

@section('content')
    @include('public.partials.page-head', [
        'wide' => false,
        'title' => __('Frequently asked questions'),
        'crumbs' => [
            ['label' => __('Home'), 'url' => route('home')],
            ['label' => __('FAQ')],
        ],
    ])

    <div class="uh-container-narrow uh-section-tight">
        @forelse($groups as $group => $faqs)
            <section class="mb-10" aria-labelledby="faq-{{ \Illuminate\Support\Str::slug($group) }}">
                <h2 id="faq-{{ \Illuminate\Support\Str::slug($group) }}" class="uh-h3">{{ $group }}</h2>
                <div class="mt-4 divide-y divide-[var(--color-line)] rounded-xl bg-paper ring-1 ring-line">
                    @foreach($faqs as $faq)
                        <details class="group px-5 py-4">
                            <summary class="flex min-h-10 cursor-pointer list-none items-center justify-between gap-4 font-semibold">
                                {{ $faq->question }}
                                <x-icon name="chevron-down" class="size-4 shrink-0 transition group-open:rotate-180" />
                            </summary>
                            <div class="uh-prose mt-3 text-sm">{!! \Stevebauman\Purify\Facades\Purify::clean($faq->answer) !!}</div>
                        </details>
                    @endforeach
                </div>
            </section>
        @empty
            <x-ui.empty icon="info" :title="__('No questions published yet')"
                        :description="__('Ask us directly and our team will reply.')">
                <a class="uh-btn-primary uh-btn-sm" href="{{ route('cms.show', 'contact') }}">{{ __('Contact us') }}</a>
            </x-ui.empty>
        @endforelse
    </div>
@endsection
