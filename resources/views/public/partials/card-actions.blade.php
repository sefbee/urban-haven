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
    $detailsIconOnly = $detailsIconOnly ?? false;
    $dividerClass = $dividerClass ?? '';
    $iconClass = $iconClass ?? 'uh-icon-action';
    $showQuickView = $showQuickView ?? false;
    $showDetails = $showDetails ?? true;
    $showContact = $showContact ?? true;
    $showFavoriteAction = $showFavoriteAction ?? false;
    $property = $property ?? null;
    $iconActions = $iconActions ?? false;
@endphp

<div class="uh-card-actions {{ $dividerClass }}">
    @if($showContact)
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
        <x-share-menu :url="$url" :title="$title" :button-class="$iconClass" />
        </div>
    @endif

    @if($showQuickView || $showDetails || $showFavoriteAction)
        <div class="uh-card-next">
            @if($showQuickView)
                <button type="button" @class([$iconActions ? 'uh-icon-action' : 'uh-btn-text'])
                        @if($iconActions) aria-label="{{ __('Quick view') }}" title="{{ __('Quick view') }}" @endif
                        @click="$dispatch('open-preview', payload)">
                    @if($iconActions)
                        <x-icon name="expand" class="size-4" />
                    @else
                        {{ __('Quick view') }}
                    @endif
                </button>
            @endif
            @if($showDetails)
                <a class="{{ $iconActions ? 'uh-icon-action' : $detailsClass }}" href="{{ $url }}"
                   @if($iconActions || $detailsIconOnly) aria-label="{{ $detailsLabel }}" title="{{ $detailsLabel }}" @endif>
                    @if($iconActions)
                        <x-icon name="arrow-right" class="size-4" />
                    @elseif($detailsIconOnly)
                        <x-icon name="external" class="size-4" />
                    @else
                        {{ $detailsLabel }}
                        <x-icon name="arrow-right" class="size-3.5" />
                    @endif
                </a>
            @endif
            @if($showFavoriteAction && $property)
                <x-save-button :property="$property" />
            @endif
        </div>
    @endif
</div>
