@php
    $phoneHref = \App\Support\PhoneNumber::telHref(\App\Models\Setting::get('phone'));
    $whatsapp = $whatsapp ?? null;
    $url = $url ?? '#';
    $title = $title ?? '';
    $trackLocation = $trackLocation ?? 'card';
    $trackPropertyId = $trackPropertyId ?? null;
    $trackProjectId = $trackProjectId ?? null;
    $detailsLabel = $detailsLabel ?? __('View details');
    $detailsClass = $detailsClass ?? 'text-sm font-semibold text-ink transition hover:text-forest';
    $dividerClass = $dividerClass ?? 'border-line';
    $iconClass = $iconClass ?? 'uh-icon-action';
    $showQuickView = $showQuickView ?? false;
@endphp

<div class="mt-auto flex items-center justify-between gap-3 border-t pt-4 {{ $dividerClass }}">
    <div class="flex items-center gap-1.5">
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

    <div class="flex items-center gap-3">
        @if($showQuickView)
            <button type="button" class="uh-btn-ghost uh-btn-sm" @click="$dispatch('open-preview', payload)">
                {{ __('Quick view') }}
            </button>
        @endif
        <a class="{{ $detailsClass }}" href="{{ $url }}">{{ $detailsLabel }}</a>
    </div>
</div>
