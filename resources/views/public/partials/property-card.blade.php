@php
    $showShortlist = $showShortlist ?? true;
    $saved = in_array($property->id, array_map('intval', session('shortlist', [])), true);
    $area = $property->area_value
        ? \App\Support\AreaConverter::format($property->area_value, $property->area_unit)
        : null;
    $compact = \App\Support\MoneyFormatter::compactBdt($property->price);
    $exact = \App\Support\MoneyFormatter::formatBdt($property->price, $property->price_basis);
    $headline = $property->listing_type !== 'rent' && $compact ? 'BDT '.$compact : $exact;
    $whatsapp = $property->whatsappEnquiryUrl();
    $listingId = $property->reference ?: (string) $property->id;
    $shareUrl = route('properties.show', $property->slug);
    $contactPhone = \App\Models\Setting::get('phone');
    $contactEmail = \App\Models\Setting::get('email');
@endphp

<article class="uh-card uh-card-hover flex flex-col">
    <div class="uh-media uh-media-zoom relative aspect-16/9">
        @include('public.partials.property-carousel', ['sizes' => '(min-width: 1024px) 380px, (min-width: 640px) 50vw, 100vw'])

        <span class="pointer-events-none absolute inset-x-3 top-3 z-10 flex flex-wrap items-start justify-between gap-2">
            <x-ui.badge tone="dark">{{ $property->listing_type === 'rent' ? __('For rent') : __('For sale') }}</x-ui.badge>
            <span class="rounded-md bg-ink/80 px-2 py-1 text-[0.6875rem] font-bold tracking-wide text-cream">#{{ $listingId }}</span>
        </span>
        @if($property->availability !== 'available')
            <span class="pointer-events-none absolute bottom-3 right-3">
                <x-ui.status :status="$property->availability" />
            </span>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-4 sm:p-5">
        <p class="uh-price text-forest">{{ $headline }}</p>
        @if($headline !== $exact)
            <p class="mt-0.5 text-xs text-[var(--color-muted)] uh-numeric">{{ $exact }}</p>
        @endif

        <h3 class="uh-h3 mt-2">
            <a class="line-clamp-2 transition hover:text-forest" href="{{ route('properties.show', $property->slug) }}" lang="{{ app()->getLocale() }}">{{ $property->title }}</a>
        </h3>

        <p class="mt-1.5 flex items-center gap-1.5 text-xs font-medium text-[var(--color-muted)]">
            <x-icon name="pin" class="size-3.5 shrink-0 text-[var(--color-gold-ink)]" />
            <span class="truncate">{{ $property->locationArea?->name }}@if($property->locationArea?->city), {{ $property->locationArea->city }}@endif</span>
        </p>

        <ul class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-[var(--color-muted)]">
            @if($area)
                <li class="flex items-center gap-1.5"><x-icon name="area" class="size-4 shrink-0" /><span class="uh-numeric">{{ $area }}</span></li>
            @endif
            @if($property->bedrooms)
                <li class="flex items-center gap-1.5"><x-icon name="bed" class="size-4 shrink-0" /><span class="uh-numeric">{{ $property->bedrooms }}</span> {{ __('bed') }}</li>
            @endif
            @if($property->bathrooms)
                <li class="flex items-center gap-1.5"><x-icon name="bath" class="size-4 shrink-0" /><span class="uh-numeric">{{ $property->bathrooms }}</span> {{ __('bath') }}</li>
            @endif
        </ul>

        <p class="mt-4 text-xs font-semibold text-ink">{{ __('Contact Urban Haven Agent') }}</p>
        <div class="mt-2 flex items-center gap-1.5 border-t border-line pt-4">
            @if(filled($contactPhone))
                <a class="uh-icon-action" href="tel:{{ preg_replace('/[^\d+]/', '', $contactPhone) }}"
                   aria-label="{{ __('Call Phone') }}">
                    <x-icon name="phone" class="size-4" />
                </a>
            @endif
            @if(filled($contactEmail))
                <a class="uh-icon-action" href="mailto:{{ $contactEmail }}?subject={{ rawurlencode($property->title) }}"
                   aria-label="{{ __('Email/Inquiry') }}">
                    <x-icon name="mail" class="size-4" />
                </a>
            @else
                <a class="uh-icon-action" href="{{ route('properties.show', $property->slug) }}#contact"
                   aria-label="{{ __('Email/Inquiry') }}">
                    <x-icon name="mail" class="size-4" />
                </a>
            @endif
            <button type="button" class="uh-icon-action" x-data="uhShare({{ \Illuminate\Support\Js::from($shareUrl) }}, {{ \Illuminate\Support\Js::from($property->title) }})"
                    @click="share()" aria-label="{{ __('Share') }}">
                <x-icon name="share" class="size-4" />
            </button>
            @if($showShortlist)
                @if($saved)
                    <form method="POST" action="{{ route('shortlist.remove', $property->id) }}" x-data="uhForm" @submit="submit">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="uh-icon-action" :disabled="submitting"
                                aria-label="{{ __('Remove :title from shortlist', ['title' => $property->title]) }}">
                            <x-icon name="heart-solid" class="size-4 text-[var(--color-danger)]" x-show="!submitting" />
                            <span class="uh-spinner" x-show="submitting" x-cloak></span>
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('shortlist.add') }}" x-data="uhForm" @submit="submit">
                        @csrf
                        <input type="hidden" name="property_id" value="{{ $property->id }}">
                        <button type="submit" class="uh-icon-action" :disabled="submitting"
                                aria-label="{{ __('Save :title to shortlist', ['title' => $property->title]) }}">
                            <x-icon name="heart" class="size-4" x-show="!submitting" />
                            <span class="uh-spinner" x-show="submitting" x-cloak></span>
                        </button>
                    </form>
                @endif
            @endif
            <a class="uh-btn-outline uh-btn-sm ml-auto" href="{{ route('properties.show', $property->slug) }}">
                {{ __('View details') }}
            </a>
            <a class="uh-btn-primary uh-btn-sm" href="{{ route('properties.show', $property->slug) }}#contact">
                {{ __('Inquire Now') }}
            </a>
            @if($whatsapp)
                <a class="uh-btn-whatsapp uh-btn-sm" href="{{ $whatsapp }}" rel="noopener">
                    <x-icon name="whatsapp" class="size-4" />
                    <span class="hidden sm:inline">{{ __('WhatsApp Us') }}</span>
                    <span class="sm:hidden">{{ __('WhatsApp') }}</span>
                </a>
            @endif
        </div>
    </div>
</article>
