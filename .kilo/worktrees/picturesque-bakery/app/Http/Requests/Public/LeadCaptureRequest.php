<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class LeadCaptureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^(?:\+?88)?01[3-9]\d{8}$/'],
            'email' => ['nullable', 'email'],
            'preferred_contact' => ['nullable', 'in:phone,whatsapp,email'],
            'message' => ['nullable', 'string', 'max:2000'],
            'property_id' => ['nullable', 'integer', 'exists:properties,id'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'utm_source' => ['nullable', 'string', 'max:120'],
            'utm_medium' => ['nullable', 'string', 'max:120'],
            'utm_campaign' => ['nullable', 'string', 'max:120'],
            'source' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (! $this->filled('property_id') && ! $this->filled('project_id')) {
                $validator->errors()->add('property_id', 'Choose a property or project.');
            }
        });
    }
}
