@props([
    'name',
    'label' => null,
    'hint' => null,
    'optional' => false,
    'id' => null,
    'errorKey' => null,
    'srLabel' => false,
    'quickAdd' => null,
])

@php
    $errorKey ??= str_replace(['[', ']'], ['.', ''], $name);
    $id ??= 'f-'.trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-');
    $invalid = $errors->has($errorKey);
    $described = array_filter([$hint ? $id.'-hint' : null, $invalid ? $id.'-error' : null]);
    $user = auth()->user();
    $canQuickAdd = match ($quickAdd) {
        'type', 'area', 'amenity' => (bool) $user?->can('reference.manage'),
        'project' => (bool) $user?->can('create', \App\Models\Project::class),
        'category' => (bool) $user?->can('create', \App\Models\Post::class),
        default => false,
    };
    $quickAddLabel = match ($quickAdd) {
        'type' => 'Add a type',
        'area' => 'Add an area',
        'project' => 'Add a project',
        'category' => 'Add a category',
        'amenity' => 'Add an amenity',
        default => 'Add',
    };
@endphp

<div class="uh-field">
    @if($label)
        <label @class(['uh-label', 'sr-only' => $srLabel]) for="{{ $id }}">
            {{ $label }}
            @if($optional)<span class="uh-label-optional">{{ __('optional') }}</span>@endif
        </label>
    @endif

    @if($canQuickAdd)
        <div class="uh-admin-select-row">
            <select id="{{ $id }}" name="{{ $name }}"
                    @if($described) aria-describedby="{{ implode(' ', $described) }}" @endif
                    @if($invalid) aria-invalid="true" @endif
                    {{ $attributes->class(['uh-select', 'uh-select-invalid' => $invalid]) }}>
                {{ $slot }}
            </select>
            <button type="button" class="uh-admin-quick-add" aria-label="{{ $quickAddLabel }}"
                    @click.prevent="$dispatch('uh-quick-add', { kind: {{ \Illuminate\Support\Js::from($quickAdd) }}, target: {{ \Illuminate\Support\Js::from($id) }} })">
                <x-icon name="plus" class="size-4" />
            </button>
        </div>
    @else
        <select id="{{ $id }}" name="{{ $name }}"
                @if($described) aria-describedby="{{ implode(' ', $described) }}" @endif
                @if($invalid) aria-invalid="true" @endif
                {{ $attributes->class(['uh-select', 'uh-select-invalid' => $invalid]) }}>
            {{ $slot }}
        </select>
    @endif

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
