@props([
    'name',
    'label' => null,
    'hint' => null,
    'showHint' => false,
    'optional' => false,
    'value' => null,
    'id' => null,
    'errorKey' => null,
    'minHeight' => '16rem',
    'placeholder' => 'Start writing…',
])

@php
    $errorKey ??= str_replace(['[', ']'], ['.', ''], $name);
    $id ??= 'f-'.trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-');
    $invalid = $errors->has($errorKey);
    $described = array_filter([$showHint && $hint ? $id.'-hint' : null, $invalid ? $id.'-error' : null]);
    $tools = [
        ['command' => 'h2', 'label' => 'Heading', 'text' => 'H2'],
        ['command' => 'h3', 'label' => 'Sub-heading', 'text' => 'H3'],
        ['command' => 'p', 'label' => 'Normal text', 'text' => '¶'],
        null,
        ['command' => 'bold', 'label' => 'Bold (Ctrl+B)', 'text' => 'B', 'class' => 'font-bold'],
        ['command' => 'italic', 'label' => 'Italic (Ctrl+I)', 'text' => 'I', 'class' => 'italic font-serif'],
        ['command' => 'underline', 'label' => 'Underline (Ctrl+U)', 'text' => 'U', 'class' => 'underline'],
        null,
        ['command' => 'insertUnorderedList', 'label' => 'Bulleted list', 'icon' => 'list'],
        ['command' => 'insertOrderedList', 'label' => 'Numbered list', 'text' => '1.'],
        ['command' => 'blockquote', 'label' => 'Quote', 'text' => '“ ”'],
        ['command' => 'link', 'label' => 'Add link (Ctrl+K)', 'icon' => 'link'],
        null,
        ['command' => 'clear', 'label' => 'Clear formatting', 'icon' => 'close'],
    ];
@endphp

<div {{ $attributes->class('uh-field') }} x-data="uhRichText">
    @if($label)
        <label class="uh-label" for="{{ $id }}" @click.prevent="focus()">
            {{ $label }}
            @if($optional)<span class="uh-label-optional">{{ __('optional') }}</span>@endif
        </label>
    @endif

    <div @class(['uh-rte', 'is-invalid' => $invalid]) :class="{ 'is-focused': focused }">
        <div class="uh-rte-toolbar" role="toolbar" aria-label="Formatting for {{ strtolower((string) $label) }}">
            @foreach($tools as $tool)
                @if($tool === null)
                    <span class="uh-rte-sep" aria-hidden="true"></span>
                @else
                    <button type="button" class="uh-rte-btn" title="{{ $tool['label'] }}" aria-label="{{ $tool['label'] }}"
                            :class="{ 'is-on': active.includes('{{ $tool['command'] }}') }"
                            @mousedown.prevent @click="run('{{ $tool['command'] }}')">
                        @isset($tool['icon'])
                            <x-icon :name="$tool['icon']" class="size-3.5" />
                        @else
                            <span class="{{ $tool['class'] ?? '' }}">{{ $tool['text'] }}</span>
                        @endisset
                    </button>
                @endif
            @endforeach
        </div>
        <div id="{{ $id }}" x-ref="editor" class="uh-rte-body uh-prose" contenteditable="true" role="textbox" aria-multiline="true"
             data-placeholder="{{ $placeholder }}" style="min-height: {{ $minHeight }}" :class="{ 'is-empty': words === 0 }"
             @if($described) aria-describedby="{{ implode(' ', $described) }}" @endif
             @if($invalid) aria-invalid="true" @endif
             @input="sync()" @paste="paste($event)" @keydown="shortcut($event)"
             @focus="focused = true" @blur="focused = false; sync()"></div>
        <div class="uh-rte-foot">
            <span x-text="words === 1 ? '1 word' : words + ' words'"></span>
        </div>
    </div>
    <textarea x-ref="input" name="{{ $name }}" class="hidden" aria-hidden="true" tabindex="-1">{{ old($errorKey, $value) }}</textarea>

    @if($showHint && $hint)
        <p class="uh-hint" id="{{ $id }}-hint">{{ $hint }}</p>
    @endif

    @error($errorKey)
        <p class="uh-error" id="{{ $id }}-error">
            <x-icon name="alert" class="mt-px size-3.5 shrink-0" />
            <span>{{ $message }}</span>
        </p>
    @enderror
</div>
