<?php

namespace App\Http\Requests\Public;

use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SiteVisitRequestForm extends LeadCaptureRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['type'], $rules['message']);

        return [
            ...$rules,
            'preferred_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'preferred_contact' => ['nullable', Rule::in(['phone', 'whatsapp', 'email'])],
        ];
    }

    public function after(): array
    {
        return [
            ...parent::after(),
            function (Validator $validator): void {
                if (blank($this->input('property_id')) && blank($this->input('project_id'))) {
                    $validator->errors()->add('property_id', 'Choose the property or project you would like to visit.');
                }

                if (! $this->filled('preferred_at') || $validator->errors()->has('preferred_at')) {
                    return;
                }

                $timezone = config('urbanhaven.display_timezone');
                $preferred = Carbon::parse($this->input('preferred_at'), $timezone);
                $now = now($timezone);

                if ($preferred->lessThan($now->copy()->addHour())) {
                    $validator->errors()->add('preferred_at', 'Choose a time at least one hour from now.');
                } elseif ($preferred->greaterThan($now->copy()->addDays((int) config('urbanhaven.lead.visit_window_days', 90)))) {
                    $validator->errors()->add('preferred_at', 'Choose a date within the next '.config('urbanhaven.lead.visit_window_days', 90).' days.');
                }
            },
        ];
    }
}
