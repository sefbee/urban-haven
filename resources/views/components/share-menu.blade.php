@props([
    'url',
    'title' => '',
    'buttonClass' => 'uh-icon-action',
    'iconClass' => 'size-4',
])

@php
    $facebookAppId = \App\Models\Setting::get('facebook_app_id');
@endphp

<div class="uh-share-control" x-data="uhShare(@js($url), @js($title), @js($facebookAppId))"
     @keydown.escape.window="open = false">
    <button type="button" {{ $attributes->class($buttonClass) }}
            @click.stop="open = ! open"
            :aria-label="open ? @js(__('Close share options')) : @js(__('Share'))"
            :aria-expanded="open.toString()"
            aria-haspopup="dialog" title="{{ __('Share') }}">
        <x-icon name="share" :class="$iconClass" />
    </button>

    <div class="uh-share-popover" x-show="open" x-cloak x-transition.opacity @click.stop @click.outside="open = false"
         role="dialog" aria-label="{{ __('Share this page') }}">
        <div class="uh-share-platforms">
            <a class="uh-share-platform is-facebook" :href="facebookHref" target="_blank" rel="noopener noreferrer" aria-label="{{ __('Share on Facebook') }}">
                <span aria-hidden="true">f</span>
            </a>
            <template x-if="messengerHref">
                <a class="uh-share-platform is-messenger" :href="messengerHref" target="_blank" rel="noopener noreferrer" aria-label="{{ __('Share on Messenger') }}">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2C6.48 2 2 6.16 2 11.3c0 2.93 1.48 5.48 3.8 7.2.2.15.32.38.32.63l.08 2.2a.8.8 0 0 0 1.12.71l2.46-1.08c.2-.09.42-.1.63-.04.98.27 2.03.42 3.15.42 5.52 0 10-4.16 10-9.3S17.52 2 12 2Zm4.9 12.23-3.12-3.31a1.2 1.2 0 0 0-1.7-.1l-2.68 2.02a.8.8 0 0 1-.97-.02L5.3 10.5a.5.5 0 0 1 .59-.8l3.13 1.98a1.2 1.2 0 0 0 1.42-.08l2.68-2.02a1.2 1.2 0 0 1 1.7.1l2.68 2.84a.5.5 0 0 1-.6.8Z"/></svg>
                </a>
            </template>
            <a class="uh-share-platform is-whatsapp" :href="whatsappHref" target="_blank" rel="noopener noreferrer" aria-label="{{ __('Share on WhatsApp') }}">
                <x-icon name="whatsapp" class="size-4" />
            </a>
            <a class="uh-share-platform is-x" :href="xHref" target="_blank" rel="noopener noreferrer" aria-label="{{ __('Share on X') }}">𝕏</a>
            <a class="uh-share-platform is-email" :href="emailHref" aria-label="{{ __('Share by email') }}">
                <x-icon name="mail" class="size-4" />
            </a>
            <button type="button" class="uh-share-platform is-copy" @click.stop="copyLink()"
                    :aria-label="copied ? @js(__('Link copied')) : @js(__('Copy link'))">
                <x-icon name="check" class="size-4" x-show="copied" x-cloak />
                <x-icon name="link" class="size-4" x-show="!copied" />
            </button>
        </div>

        <p class="uh-share-copied" x-show="copied" x-cloak role="status">{{ __('Link copied') }}</p>
    </div>
</div>
