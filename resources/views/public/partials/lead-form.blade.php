@php
    $formType = $formType ?? 'inquiry';
    $isVisit = $formType === 'visit';
    $prefix = $prefix ?? ($isVisit ? 'visit' : 'enq');
    $leadType = $leadType ?? null;
    $source = $source ?? null;
    $submitLabel = $submitLabel ?? ($isVisit ? __('Request a visit') : __('Send enquiry'));
    $showMessage = $showMessage ?? true;
    $compact = $compact ?? false;
    $formClass = $formClass ?? '';
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
    $fieldError = fn (string $field) => "<p id=\"{$prefix}-{$field}-error\" class=\"uh-error\" x-cloak x-show=\"fieldError('{$field}')\" role=\"alert\"><span class=\"uh-error-mark\" aria-hidden=\"true\">!</span><span x-text=\"fieldError('{$field}')\"></span></p>";
@endphp

<div x-data="uhLeadForm(@js($isVisit ? 'visit_request' : ($leadType ?? 'inquiry')))">
    <div x-cloak x-show="done" x-transition.opacity.duration.300ms class="uh-form-success" role="status" tabindex="-1" x-effect="done && $nextTick(() => $el.focus())">
        <p class="uh-h4">{{ $isVisit ? __('Visit request received') : __('Enquiry received') }}</p>
        <p class="text-sm leading-relaxed" x-text="message"></p>
        @if($isVisit)
            <p class="text-sm text-[var(--uh-muted)]">{{ __('Dhaka time. We will call to confirm before you travel.') }}</p>
        @endif
    </div>

    <form method="POST" action="{{ $isVisit ? route('visits.store') : route('inquiries.store') }}" @class(['uh-lead-form', $formClass, 'gap-3' => $compact]) novalidate
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

        @if($isVisit)
            <div>
                <x-ui.input name="name" id="{{ $prefix }}-name" :label="__('Your name')" autocomplete="name" required maxlength="120" />
                {!! $fieldError('name') !!}
            </div>
            <div>
                <x-ui.input name="phone" id="{{ $prefix }}-phone" :label="__('Mobile number')" type="tel" dir="ltr" inputmode="tel" autocomplete="tel" :placeholder="__('01XXXXXXXXX')" maxlength="24" required />
                {!! $fieldError('phone') !!}
            </div>
            <div>
                <x-ui.input name="email" id="{{ $prefix }}-email" :label="__('Email')" type="email" dir="ltr" autocomplete="email" maxlength="190" optional />
                {!! $fieldError('email') !!}
            </div>
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
            <section class="space-y-4 border-b border-[var(--uh-line)] pb-5" aria-labelledby="{{ $prefix }}-details-heading">
                <h3 id="{{ $prefix }}-details-heading" class="uh-h4">{{ __('Your Details') }}</h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-ui.input name="full_name" id="{{ $prefix }}-name" :label="__('Full Name *')" autocomplete="name" required maxlength="120" x-bind:aria-invalid="fieldError('full_name') ? 'true' : 'false'" x-bind:aria-errormessage="@js($prefix.'-full_name-error')" />
                        {!! $fieldError('full_name') !!}
                    </div>
                    <div>
                        <x-ui.input name="email" id="{{ $prefix }}-email" :label="__('Email Address *')" type="email" dir="ltr" autocomplete="email" maxlength="190" required placeholder="you@example.com" showHint :hint="__('We will send a confirmation of your inquiry here.')" x-bind:aria-invalid="fieldError('email') ? 'true' : 'false'" x-bind:aria-errormessage="@js($prefix.'-email-error')" />
                        {!! $fieldError('email') !!}
                    </div>
                    <div class="sm:col-span-2">
                        <x-ui.input name="phone" id="{{ $prefix }}-phone" :label="__('Phone Number *')" type="tel" dir="ltr" inputmode="tel" autocomplete="tel" placeholder="01XXXXXXXXX" maxlength="24" required showHint :hint="__('Please use a number we can reach, ideally one with WhatsApp.')" x-bind:aria-invalid="fieldError('phone') ? 'true' : 'false'" x-bind:aria-errormessage="@js($prefix.'-phone-error')" />
                        {!! $fieldError('phone') !!}
                    </div>
                </div>
            </section>

            <section class="space-y-4 border-b border-[var(--uh-line)] pb-5" aria-labelledby="{{ $prefix }}-property-heading">
                <h3 id="{{ $prefix }}-property-heading" class="uh-h4">{{ __('Your Property') }}</h3>
                <fieldset x-bind:aria-invalid="fieldError('property_type') ? 'true' : 'false'" x-bind:aria-errormessage="@js($prefix.'-property_type-error')">
                    <legend class="uh-label">{{ __('Property Type *') }}</legend>
                    <div class="uh-property-type-options mt-2 grid gap-2 sm:grid-cols-2">
                        @foreach(['residential' => 'Residential', 'apartment_multi_family' => 'Apartment / Multi-family', 'commercial' => 'Commercial', 'land_plot' => 'Land / Plot'] as $value => $label)
                            <label class="uh-check"><input type="radio" name="property_type" value="{{ $value }}" required @checked(old('property_type') === $value)><span>{{ __($label) }}</span></label>
                        @endforeach
                    </div>
                    {!! $fieldError('property_type') !!}
                </fieldset>
                <div>
                    <x-ui.input name="property_location" id="{{ $prefix }}-location" :label="__('Location *')" required maxlength="255" placeholder="Gulshan 2, Banani Block F, Dhanmondi 8/A" showHint :hint="__('Area or address.')" x-bind:aria-invalid="fieldError('property_location') ? 'true' : 'false'" x-bind:aria-errormessage="@js($prefix.'-property_location-error')" />
                    {!! $fieldError('property_location') !!}
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-ui.input name="number_of_units" id="{{ $prefix }}-units" type="number" min="1" step="1" :label="__('Number of Units')" :value="old('number_of_units', 1)" showHint :hint="__('How many flats, floors or shops need to be managed?')" x-bind:aria-invalid="fieldError('number_of_units') ? 'true' : 'false'" x-bind:aria-errormessage="@js($prefix.'-number_of_units-error')" />
                        {!! $fieldError('number_of_units') !!}
                    </div>
                    <fieldset x-bind:aria-invalid="fieldError('current_status') ? 'true' : 'false'" x-bind:aria-errormessage="@js($prefix.'-current_status-error')">
                        <legend class="uh-label">{{ __('Current Status') }}</legend>
                        <div class="mt-2 flex flex-wrap gap-3">
                            @foreach(['occupied' => 'Occupied', 'vacant' => 'Vacant', 'under_construction' => 'Under construction'] as $value => $label)
                                <label class="uh-check"><input type="radio" name="current_status" value="{{ $value }}" @checked(old('current_status') === $value)><span>{{ __($label) }}</span></label>
                            @endforeach
                        </div>
                        {!! $fieldError('current_status') !!}
                    </fieldset>
                </div>
            </section>

            <section class="space-y-4 border-b border-[var(--uh-line)] pb-5" aria-labelledby="{{ $prefix }}-help-heading">
                <h3 id="{{ $prefix }}-help-heading" class="uh-h4">{{ __('How Can We Help?') }}</h3>
                <fieldset x-bind:aria-invalid="fieldError('service_type') ? 'true' : 'false'" x-bind:aria-errormessage="@js($prefix.'-service_type-error')">
                    <legend class="uh-label">{{ __('Service *') }}</legend>
                    <div class="uh-service-options mt-2 grid gap-3">
                        @foreach(['full_service_management' => ['Full-Service Property Management', 'Complete care of your property, handled by our team.', 'building'], 'tenant_finding_leasing' => ['Tenant Finding & Leasing', 'Find and screen suitable tenants.', 'key'], 'rent_collection_maintenance' => ['Rent Collection & Maintenance', 'Regular upkeep and rent handling.', 'settings']] as $value => $service)
                            <label class="uh-check uh-service-option"><input type="radio" name="service_type" value="{{ $value }}" required @checked(old('service_type') === $value)><span class="uh-service-option-icon" aria-hidden="true"><x-icon :name="$service[2]" class="size-5" /></span><span class="uh-service-option-copy"><strong>{{ __($service[0]) }}</strong><span>{{ __($service[1]) }}</span></span><span class="uh-service-option-indicator" aria-hidden="true"></span></label>
                        @endforeach
                    </div>
                    {!! $fieldError('service_type') !!}
                </fieldset>
                <div>
                    <x-ui.textarea name="details" id="{{ $prefix }}-details" :label="__('Details')" rows="4" maxlength="2000" optional placeholder="Tell us more about your property and what you need." showHint :hint="__('Anything we should know? Building age, current rent, specific issues or your goals.')" x-bind:aria-invalid="fieldError('details') ? 'true' : 'false'" x-bind:aria-errormessage="@js($prefix.'-details-error')" />
                    {!! $fieldError('details') !!}
                </div>
            </section>

            <section class="space-y-4" aria-labelledby="{{ $prefix }}-reach-heading">
                <h3 id="{{ $prefix }}-reach-heading" class="uh-h4">{{ __('How Should We Reach You?') }}</h3>
                <div class="uh-contact-preference-grid">
                    <fieldset class="uh-contact-preference-group" x-bind:aria-invalid="fieldError('preferred_contact_method') ? 'true' : 'false'" x-bind:aria-errormessage="@js($prefix.'-preferred_contact_method-error')">
                        <legend class="uh-label">{{ __('Preferred Contact Method') }}</legend>
                        <div class="uh-preference-options">
                            @foreach(['phone' => 'Phone Call', 'whatsapp' => 'WhatsApp', 'email' => 'Email'] as $value => $label)
                                <label class="uh-check uh-preference-option"><input type="radio" name="preferred_contact_method" value="{{ $value }}" @checked(old('preferred_contact_method') === $value)><span>{{ __($label) }}</span></label>
                            @endforeach
                        </div>
                        {!! $fieldError('preferred_contact_method') !!}
                    </fieldset>
                    <fieldset class="uh-contact-preference-group" x-bind:aria-invalid="fieldError('preferred_contact_time') ? 'true' : 'false'" x-bind:aria-errormessage="@js($prefix.'-preferred_contact_time-error')">
                        <legend class="uh-label">{{ __('Best Time to Contact') }}</legend>
                        <div class="uh-preference-options">
                            @foreach(['morning' => 'Morning (9–12)', 'afternoon' => 'Afternoon (12–5)', 'evening' => 'Evening (5–9)'] as $value => $label)
                                <label class="uh-check uh-preference-option"><input type="radio" name="preferred_contact_time" value="{{ $value }}" @checked(old('preferred_contact_time') === $value)><span>{{ __($label) }}</span></label>
                            @endforeach
                        </div>
                        {!! $fieldError('preferred_contact_time') !!}
                    </fieldset>
                </div>
            </section>
        @endif

        <div class="uh-consent-card">
            <label class="uh-check uh-consent-check">
                <input type="checkbox" name="{{ $isVisit ? 'consent_given' : 'contact_consent' }}" value="1" required @checked(old($isVisit ? 'consent_given' : 'contact_consent')) x-bind:aria-invalid="fieldError(@js($isVisit ? 'consent_given' : 'contact_consent')) ? 'true' : 'false'" x-bind:aria-errormessage="@js($prefix.'-'.($isVisit ? 'consent_given' : 'contact_consent').'-error')">
                <span class="text-xs leading-relaxed text-[var(--uh-muted)]">{{ $isVisit ? $consentText : __('I agree to be contacted by Urban Haven about my inquiry.') }}</span>
            </label>
            {!! $fieldError($isVisit ? 'consent_given' : 'contact_consent') !!}
            @unless($isVisit)<p class="uh-consent-note">{{ __('Your information is used only to respond to your inquiry and is not shared with third parties.') }}</p>@endunless
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
