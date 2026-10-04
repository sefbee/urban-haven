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

    <div class="uh-container-narrow uh-section-tight pt-0">
        @forelse($groups as $group => $faqs)
            <section class="uh-faq-group" aria-labelledby="faq-{{ \Illuminate\Support\Str::slug($group) }}">
                <h2 id="faq-{{ \Illuminate\Support\Str::slug($group) }}" class="uh-h4">{{ $group }}</h2>
                <div class="uh-faq">
                    @foreach($faqs as $faq)
                        <details>
                            <summary>
                                {{ $faq->question }}
                                <x-icon name="plus" class="uh-faq-icon size-4 shrink-0" />
                            </summary>
                            <div class="uh-prose">{!! \Stevebauman\Purify\Facades\Purify::clean($faq->answer) !!}</div>
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

        @if($groups->isNotEmpty())
            <aside class="uh-next">
                <p class="uh-h3">{{ __('Still have a question? Ask our team directly.') }}</p>
                <div class="uh-next-links">
                    <a class="uh-btn-primary uh-btn-sm" href="{{ route('cms.show', 'contact') }}">{{ __('Contact us') }}</a>
                </div>
            </aside>
        @endif
    </div>
@endsection
