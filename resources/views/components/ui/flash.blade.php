@php
    $bag = $errors ?? null;
    $hasErrors = $bag && $bag->any();
@endphp

@if(session('status') || session('error') || $hasErrors)
    <div {{ $attributes->class('space-y-3') }} aria-live="polite">
        @if(session('status'))
            <x-ui.alert tone="success" dismissible>{{ session('status') }}</x-ui.alert>
        @endif

        @if(session('error'))
            <x-ui.alert tone="danger" dismissible>{{ session('error') }}</x-ui.alert>
        @endif

        @if($hasErrors)
            <x-ui.alert tone="danger">
                @if($bag->count() === 1)
                    {{ $bag->first() }}
                @else
                    <p class="font-medium">{{ __('Please check the following:') }}</p>
                    <ul class="mt-1.5 list-disc space-y-1 pl-4">
                        @foreach($bag->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.alert>
        @endif
    </div>
@endif
