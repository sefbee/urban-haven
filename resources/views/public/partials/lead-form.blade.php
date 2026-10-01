@php
    $formType = $formType ?? 'inquiry';
    $isVisit = $formType === 'visit';
    $prefix = $prefix ?? ($isVisit ? 'visit' : 'enq');
    $leadType = $leadType ?? null;
    $source = $source ?? null;
    $submitLabel = $submitLabel ?? ($isVisit ? __('Request a visit') : __('Send enquiry'));
    $showMessage = $showMessage ?? true;
    $messageLabel = $messageLabel ?? __('Anything we should know?');
    $messagePlaceholder = $messagePlaceholder ?? __('Preferred floor, timeline, budget…');
    $consentText = \App\Models\Setting::get('consent_text') ?: \App\Support\SettingsSchema::defaultConsentText();
    $timezone = config('urbanhaven.display_timezone');
    $minVisit = now($timezone)->addHours(2)->startOfHour()->format('Y-m-d\TH:i');
    $maxVisit = now($timezone)->addDays((int) config('urbanhaven.lead.visit_window_days', 90))->format('Y-m-d\TH:i');
    $fieldError = fn (string $field) => "<p class=\"uh-error\" x-cloak x-show=\"fieldError('{$field}')\"><span x-text=\"fieldError('{$field}')\"></span></p>";
@endphp

<div x-data="uhLeadForm(@js($isVisit ? 'visit_request' : ($leadType ?? 'inquiry')))">
    <div x-cloak x-show="done" class="uh-alert uh-alert-success" role="status" tabindex="-1" x-effect="done && $nextTick(() => $el.focus())">
        <x-icon name="check-circle" class="mt-0.5 size-4 shrink-0" />
        <div>
            <p class="font-semibold">{{ $isVisit ? __('Visit request received') : __('Enquiry received') }}</p>
            <p class="mt-1" x-text="message"></p>
        </div>
    </div>

    <form method="POST" action="{{ $isVisit ? route('visits.store') : route('inquiries.store') }}" class="space-y-4" novalidate
          x-show="!done" @submit="submit($event)" @focusin.once="start()">
        @csrf
        <input type="hidden" name="submission_token" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
        @isset($propertyId)
            <input type="hidden" name="property_id" value="{{ $propertyId }}">
        @endisset
        @isset($projectId)
            <input type="hidden" name="project_id" value="{{ $projectId }}">
        @endisset
        @if($leadType && ! $isVisit)
            <input type="hidden" name="type" value="{{ $leadType }}">
        @endif
        @if($source)
            <input type="hidden" name="source" value="{{ $source }}">
        @endif
        <div class="absolute -left-[9999px] size-px overflow-hidden" aria-hidden="true">
            <label for="{{ $prefix }}-website">Website</label>
            <input type="text" id="{{ $prefix }}-website" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div x-cloak x-show="error" class="uh-alert uh-alert-danger" role="alert">
            <x-icon name="alert" class="mt-0.5 size-4 shrink-0" />
            <p x-text="error"></p>
        </div>
        @error('form')
            <x-ui.alert tone="danger">{{ $message }}</x-ui.alert>
        @enderror

        <div>
            <x-ui.input name="name" id="{{ $prefix }}-name" :label="__('Your name')" autocomplete="name" required maxlength="120" />
            {!! $fieldError('name') !!}
        </div>
        <div>
            <x-ui.input name="phone" id="{{ $prefix }}-phone" :label="__('Mobile number')" type="tel" dir="ltr"
                        inputmode="tel" autocomplete="tel" placeholder="01XXXXXXXXX" maxlength="24"
                        :hint="__('Bangladeshi mobile (01XXXXXXXXX) or an international number starting with +')" required />
            {!! $fieldError('phone') !!}
        </div>
        <div>
            <x-ui.input name="email" id="{{ $prefix }}-email" :label="__('Email')" type="email" dir="ltr"
                        autocomplete="email" maxlength="190" optional />
            {!! $fieldError('email') !!}
        </div>

        @if($isVisit)
            <div>
                <x-ui.input name="preferred_at" id="{{ $prefix }}-at" type="datetime-local" :label="__('Preferred date and time')"
                            min="{{ $minVisit }}" max="{{ $maxVisit }}" step="900" required
                            :hint="__('Dhaka time. We will call to confirm before you travel.')" />
                {!! $fieldError('preferred_at') !!}
            </div>
            <div>
                <x-ui.textarea name="notes" id="{{ $prefix }}-notes" :label="__('Notes for the visit')" rows="2" maxlength="1000" optional />
                {!! $fieldError('notes') !!}
            </div>
        @else
            <x-ui.select name="preferred_contact" id="{{ $prefix }}-contact" :label="__('Best way to reach you')">
                <option value="phone" @selected(old('preferred_contact') === 'phone')>{{ __('Phone call') }}</option>
                <option value="whatsapp" @selected(old('preferred_contact') === 'whatsapp')>{{ __('WhatsApp') }}</option>
                <option value="email" @selected(old('preferred_contact') === 'email')>{{ __('Email') }}</option>
            </x-ui.select>
            @if($showMessage)
                <div>
                    <x-ui.textarea name="message" id="{{ $prefix }}-message" :label="$messageLabel" rows="3" maxlength="2000" optional
                                   :placeholder="$messagePlaceholder" />
                    {!! $fieldError('message') !!}
                </div>
            @endif
        @endif

        <div>
            <label class="uh-check items-start">
                <input type="checkbox" name="consent_given" value="1" required @checked(old('consent_given'))>
                <span class="text-xs leading-relaxed text-[var(--color-muted)]">{{ $consentText }}</span>
            </label>
            @error('consent_given')
                <p class="uh-error"><span>{{ $message }}</span></p>
            @enderror
            {!! $fieldError('consent_given') !!}
            {!! $fieldError('submission_token') !!}
        </div>

        <button type="submit" class="uh-btn-primary uh-btn-block" :disabled="submitting" :aria-busy="submitting.toString()">
            <span class="uh-spinner" x-show="submitting" x-cloak></span>
            <span x-text="submitting ? @js(__('Sending…')) : @js($submitLabel)">{{ $submitLabel }}</span>
        </button>
    </form>
</div>
