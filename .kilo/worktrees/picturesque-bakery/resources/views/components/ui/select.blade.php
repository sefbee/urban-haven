@props([
    'name',
    'label' => null,
    'hint' => null,
    'optional' => false,
    'id' => null,
    'errorKey' => null,
    'srLabel' => false,
])

@php
    $errorKey ??= str_replace(['[', ']'], ['.', ''], $name);
    $id ??= 'f-'.trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-');
    $invalid = $errors->has($errorKey);
    $described = array_filter([$hint ? $id.'-hint' : null, $invalid ? $id.'-error' : null]);
@endphp

<div class="uh-field">
    @if($label)
        <label @class(['uh-label', 'sr-only' => $srLabel]) for="{{ $id }}">
            {{ $label }}
            @if($optional)<span class="uh-label-optional">{{ __('optional') }}</span>@endif
        </label>
    @endif

    <select id="{{ $id }}" name="{{ $name }}"
            @if($described) aria-describedby="{{ implode(' ', $described) }}" @endif
            @if($invalid) aria-invalid="true" @endif
            {{ $attributes->class(['uh-select', 'uh-select-invalid' => $invalid]) }}>
        {{ $slot }}
    </select>

    @if($hint)
        <p class="uh-hint" id="{{ $id }}-hint">{{ $hint }}</p>
    @endif

    @error($errorKey)
        <p class="uh-error" id="{{ $id }}-error">
            <x-icon name="alert" class="mt-px size-3.5 shrink-0" />
            <span>{{ $message }}</span>
        </p>
    @enderror
</div>
