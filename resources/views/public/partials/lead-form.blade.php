@php
    $formType = $formType ?? 'inquiry';
    $isVisit = $formType === 'visit';
    $prefix = $prefix ?? ($isVisit ? 'visit' : 'enq');
    $leadType = $leadType ?? null;
    $source = $source ?? null;
    $submitLabel = $submitLabel ?? ($isVisit ? __('Request a visit') : __('Send enquiry'));
    $showMessage = $showMessage ?? true;
    $compact = $compact ?? false;
    $messageLabel = $messageLabel ?? __('Anything we should know?');
    $messagePlaceholder = $messagePlaceholder ?? __('Preferred floor, timeline, budget…');
    $consentText = \App\Models\Setting::get('consent_text') ?: \App\Support\SettingsSchema::defaultConsentText();
    $timezone = config('urbanhaven.display_timezone');
    $minVisit = now($timezone)->addHours(2)->startOfHour()->format('Y-m-d\TH:i');
    $maxVisit = now($timezone)->addDays((int) config('urbanhaven.lead.visit_window_days', 90))->format('Y-m-d\TH:i');
    $showNextSteps = $showNextSteps ?? true;
    $showVisitNotes = $showVisitNotes ?? true;
    $showSuggestedVisitTimes = $showSuggestedVisitTimes ?? true;
    $officeHours = \App\Models\Setting::get('office_hours');
    $nextSteps = $isVisit
        ? array_values(array_filter([
            __('We call you to confirm the time before you travel.'),
            ($addressOnBooking ?? false) ? __('The exact address is shared when you book a visit.') : null,
        ]))
        : array_values(array_filter([
            __('Our sales team reads your message.'),
            __('We reply by the method you choose above.'),
            $officeHours ? __('Office hours: :hours', ['hours' => $officeHours]) : null,
        ]));
    $intents = $showMessage && ! $isVisit ? ($intents ?? []) : [];
    $visitSlots = [];
    if ($isVisit) {
        $earliest = \Illuminate\Support\Carbon::parse($minVisit, $timezone);
        $tomorrow = now($timezone)->addDay()->startOfDay();
        $candidates = [
            $tomorrow->copy()->setTime(11, 0),
            $tomorrow->copy()->setTime(16, 0),
            $tomorrow->copy()->next(\Carbon\CarbonInterface::FRIDAY)->setTime(11, 0),
            $tomorrow->copy()->next(\Carbon\CarbonInterface::SATURDAY)->setTime(16, 0),
        ];
        foreach ($candidates as $slot) {
            if ($slot->gte($earliest) && ! array_key_exists($slot->format('Y-m-d\TH:i'), $visitSlots)) {
                $visitSlots[$slot->format('Y-m-d\TH:i')] = $slot->isSameDay($tomorrow)
                    ? __('Tomorrow, :time', ['time' => $slot->format('g a')])
                    : $slot->translatedFormat('D j M').', '.$slot->format('g a');
            }
        }
    }
    $fieldError = fn (string $field) => "<p class=\"uh-error\" x-cloak x-show=\"fieldError('{$field}')\"><span x-text=\"fieldError('{$field}')\"></span></p>";
@endphp

<div x-data="uhLeadForm(@js($isVisit ? 'visit_request' : ($leadType ?? 'inquiry')))">
    <div x-cloak x-show="done" x-transition.opacity.duration.300ms class="uh-form-success" role="status" tabindex="-1" x-effect="done && $nextTick(() => $el.focus())">
        <p class="uh-h4">{{ $isVisit ? __('Visit request received') : __('Enquiry received') }}</p>
        <p class="text-sm leading-relaxed" x-text="message"></p>
        @if($isVisit)
            <p class="text-sm text-[var(--uh-muted)]">{{ __('Dhaka time. We will call to confirm before you travel.') }}</p>
        @endif
    </div>

    <form method="POST" action="{{ $isVisit ? route('visits.store') : route('inquiries.store') }}" @class(['uh-lead-form', 'gap-3' => $compact]) novalidate
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
            <label for="{{ $prefix }}-website">{{ __('Website') }}</label>
            <input type="text" id="{{ $prefix }}-website" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div x-cloak x-show="error" class="uh-alert uh-alert-danger" role="alert">
            <x-icon name="alert" class="mt-0.5 size-4 shrink-0" />
            <p x-text="error"></p>
        </div>
        @error('form')
            <x-ui.alert tone="danger">{{ $message }}</x-ui.alert>
        @enderror

        @if($compact && ! $isVisit)
            <div class="grid gap-3 sm:grid-cols-2">
        @endif
        <div>
            <x-ui.input name="name" id="{{ $prefix }}-name" :label="__('Your name')" autocomplete="name" required maxlength="120" />
            {!! $fieldError('name') !!}
        </div>
        <div>
            <x-ui.input name="phone" id="{{ $prefix }}-phone" :label="__('Mobile number')" type="tel" dir="ltr"
                        inputmode="tel" autocomplete="tel" :placeholder="__('01XXXXXXXXX')" maxlength="24"
                        pattern="^(?:(?:\+?880|0)?1[3-9]\d{8}|\+[1-9]\d{7,14})$"
                        :hint="$compact ? __('01XXXXXXXXX or +country code') : __('Bangladeshi mobile (01XXXXXXXXX) or an international number starting with +')" required />
            {!! $fieldError('phone') !!}
        </div>
        @if($compact && ! $isVisit)
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <x-ui.input name="email" id="{{ $prefix }}-email" :label="__('Email')" type="email" dir="ltr"
                                autocomplete="email" maxlength="190" optional />
                    {!! $fieldError('email') !!}
                </div>
                <x-ui.select name="preferred_contact" id="{{ $prefix }}-contact" :label="__('Best way to reach you')">
                    <option value="phone" @selected(old('preferred_contact') === 'phone')>{{ __('Phone call') }}</option>
                    <option value="whatsapp" @selected(old('preferred_contact') === 'whatsapp')>{{ __('WhatsApp') }}</option>
                    <option value="email" @selected(old('preferred_contact') === 'email')>{{ __('Email') }}</option>
                </x-ui.select>
            </div>
        @else
            <div>
                <x-ui.input name="email" id="{{ $prefix }}-email" :label="__('Email')" type="email" dir="ltr"
                            autocomplete="email" maxlength="190" optional />
                {!! $fieldError('email') !!}
            </div>
        @endif

        @if($isVisit)
            <div @input="if ($event.target.name === 'preferred_at') slot = $event.target.value">
                @if($showSuggestedVisitTimes && $visitSlots)
                    <div class="uh-intents" role="group" aria-label="{{ __('Suggested times') }}">
                        <p class="uh-intents-label">{{ __('Suggested times') }}</p>
                        @foreach($visitSlots as $value => $label)
                            <button type="button" class="uh-intent" :aria-pressed="(slot === @js($value)).toString()" aria-pressed="false"
                                    @click="useSlot(@js($value))">{{ $label }}</button>
                        @endforeach
                    </div>
                @endif
                <x-ui.input name="preferred_at" id="{{ $prefix }}-at" type="datetime-local" :label="__('Preferred date and time')"
                            min="{{ $minVisit }}" max="{{ $maxVisit }}" step="900" required
                            :hint="__('Dhaka time. We will call to confirm before you travel.')" />
                {!! $fieldError('preferred_at') !!}
            </div>
            @if($showVisitNotes)
                <div>
                    <x-ui.textarea name="notes" id="{{ $prefix }}-notes" :label="__('Notes for the visit')" rows="2" maxlength="1000" optional />
                    {!! $fieldError('notes') !!}
                </div>
            @endif
        @else
            @unless($compact)
                <x-ui.select name="preferred_contact" id="{{ $prefix }}-contact" :label="__('Best way to reach you')">
                    <option value="phone" @selected(old('preferred_contact') === 'phone')>{{ __('Phone call') }}</option>
                    <option value="whatsapp" @selected(old('preferred_contact') === 'whatsapp')>{{ __('WhatsApp') }}</option>
                    <option value="email" @selected(old('preferred_contact') === 'email')>{{ __('Email') }}</option>
                </x-ui.select>
            @endunless
            @if($showMessage)
                <div>
                    @if($intents)
                        <div class="uh-intents" role="group" aria-label="{{ __('What would you like to know?') }}">
                            <p class="uh-intents-label">{{ __('What would you like to know?') }}</p>
                            @foreach($intents as $label => $text)
                                <button type="button" class="uh-intent" :aria-pressed="(intent === @js($text)).toString()" aria-pressed="false"
                                        @click="useIntent(@js($text))">{{ $label }}</button>
                            @endforeach
                        </div>
                    @endif
                    <x-ui.textarea name="message" id="{{ $prefix }}-message" :label="$messageLabel" :rows="$compact ? 2 : 3" maxlength="2000" optional
                                   :placeholder="$messagePlaceholder" />
                    {!! $fieldError('message') !!}
                </div>
            @endif
        @endif

        <div>
            <label class="uh-check items-start">
                <input type="checkbox" name="consent_given" value="1" required @checked(old('consent_given'))>
                <span class="text-xs leading-relaxed text-[var(--uh-muted)]">{{ $consentText }}</span>
            </label>
            {!! $fieldError('consent_given') !!}
            {!! $fieldError('submission_token') !!}
        </div>

        <button type="submit" class="uh-btn-primary uh-btn-block" :disabled="submitting" :aria-busy="submitting.toString()">
            <span class="uh-spinner" x-show="submitting" x-cloak></span>
            <span x-text="submitting ? @js(__('Sending…')) : @js($submitLabel)">{{ $submitLabel }}</span>
        </button>

        @if($showNextSteps)
            <p class="uh-next-steps">{{ implode(' ', $nextSteps) }}</p>
        @endif
    </form>
</div>
