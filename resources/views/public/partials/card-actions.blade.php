@php
    $phoneHref = \App\Support\PhoneNumber::telHref(\App\Models\Setting::get('phone'));
    $whatsapp = $whatsapp ?? null;
    $url = $url ?? '#';
    $title = $title ?? '';
    $trackLocation = $trackLocation ?? 'card';
    $trackPropertyId = $trackPropertyId ?? null;
    $trackProjectId = $trackProjectId ?? null;
    $detailsLabel = $detailsLabel ?? __('Explore');
    $detailsClass = $detailsClass ?? 'uh-arrow-link';
    $dividerClass = $dividerClass ?? '';
    $iconClass = $iconClass ?? 'uh-icon-action';
    $showQuickView = $showQuickView ?? false;
    $showDetails = $showDetails ?? true;
@endphp

<div class="uh-card-actions {{ $dividerClass }}">
    <div class="uh-card-contact">
        @if($phoneHref)
            <a class="{{ $iconClass }}" href="{{ $phoneHref }}"
               data-track="phone_click" data-track-location="{{ $trackLocation }}"
               @if($trackPropertyId) data-track-property-id="{{ $trackPropertyId }}" @endif
               @if($trackProjectId) data-track-project_id="{{ $trackProjectId }}" @endif
               aria-label="{{ __('Call') }}">
                <x-icon name="phone" class="size-4" />
            </a>
        @endif
        @if($whatsapp)
            <a class="{{ $iconClass }}" href="{{ $whatsapp }}" rel="noopener" target="_blank"
               data-track="whatsapp_click" data-track-location="{{ $trackLocation }}"
               @if($trackPropertyId) data-track-property-id="{{ $trackPropertyId }}" @endif
               @if($trackProjectId) data-track-project_id="{{ $trackProjectId }}" @endif
               aria-label="{{ __('WhatsApp') }}">
                <x-icon name="whatsapp" class="size-4" />
            </a>
        @endif
        <button type="button" class="{{ $iconClass }}"
                x-data="uhShare({{ \Illuminate\Support\Js::from($url) }}, {{ \Illuminate\Support\Js::from($title) }})"
                @click="share()" aria-label="{{ __('Share') }}">
            <x-icon name="share" class="size-4" />
        </button>
    </div>

    @if($showQuickView || $showDetails)
        <div class="uh-card-next">
            @if($showQuickView)
                <button type="button" class="uh-btn-text" @click="$dispatch('open-preview', payload)">
                    {{ __('Quick view') }}
                </button>
            @endif
            @if($showDetails)
                <a class="{{ $detailsClass }}" href="{{ $url }}">
                    {{ $detailsLabel }}
                    <x-icon name="arrow-right" class="size-3.5" />
                </a>
            @endif
        </div>
    @endif
</div>
