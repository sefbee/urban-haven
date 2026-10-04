@props(['property', 'list' => 'shortlist', 'variant' => 'icon'])

@php
    $id = (int) $property->id;
    $isCompare = $list === 'compare';
    $addLabel = $isCompare ? __('Add :title to compare', ['title' => $property->title]) : __('Save :title to shortlist', ['title' => $property->title]);
    $removeLabel = $isCompare ? __('Remove :title from compare', ['title' => $property->title]) : __('Remove :title from shortlist', ['title' => $property->title]);
@endphp

<button type="button" x-data
        @click.prevent.stop="$store.saved.toggle('{{ $list }}', {{ $id }})"
        :aria-pressed="$store.saved.has('{{ $list }}', {{ $id }}).toString()"
        :aria-label="$store.saved.has('{{ $list }}', {{ $id }}) ? @js($removeLabel) : @js($addLabel)"
        aria-label="{{ $addLabel }}"
        {{ $attributes->class([
            'uh-icon-action' => $variant === 'icon',
            'uh-btn-text' => $variant === 'button',
        ]) }}>
    @if($variant === 'button')
        @if($isCompare)
            <span x-text="$store.saved.has('compare', {{ $id }}) ? @js(__('In compare')) : @js(__('Compare'))">{{ __('Compare') }}</span>
        @else
            <span x-text="$store.saved.has('shortlist', {{ $id }}) ? @js(__('Saved')) : @js(__('Save'))">{{ __('Save') }}</span>
        @endif
    @elseif($isCompare)
        <x-icon name="compare" class="size-4" />
    @else
        <x-icon name="heart" class="size-4" x-show="!$store.saved.has('shortlist', {{ $id }})" />
        <x-icon name="heart-solid" class="size-4 text-brass" x-show="$store.saved.has('shortlist', {{ $id }})" x-cloak />
    @endif
</button>
