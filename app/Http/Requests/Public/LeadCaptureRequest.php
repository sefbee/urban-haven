<?php

namespace App\Http\Requests\Public;

use App\Models\Lead;
use App\Models\Project;
use App\Models\Property;
use App\Rules\PhoneNumberRule;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class LeadCaptureRequest extends FormRequest
{
    private bool $propertyManagementSubmission = false;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->propertyManagementSubmission = array_key_exists('full_name', $this->getInputSource()->all());

        $normalized = [
            'name' => is_string($this->input('name')) ? trim($this->input('name')) : $this->input('name'),
            'email' => is_string($this->input('email')) ? trim($this->input('email')) : $this->input('email'),
            'phone' => is_string($this->input('phone')) ? trim($this->input('phone')) : $this->input('phone'),
        ];

        if ($this->propertyManagementSubmission) {
            $normalized['full_name'] = is_string($this->input('full_name')) ? trim($this->input('full_name')) : $this->input('full_name');
        }

        $this->merge($normalized);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['nullable', Rule::in(array_diff(Lead::TYPES, ['visit_request']))],
            'full_name' => ['nullable', 'string', 'min:2', 'max:120'],
            'name' => ['nullable', 'string', 'min:2', 'max:120'],
            'phone' => ['required', 'string', 'max:24', new PhoneNumberRule],
            'email' => ['nullable', 'email:rfc', 'max:190'],
            'preferred_contact' => ['nullable', Rule::in(['phone', 'whatsapp', 'email'])],
            'preferred_contact_method' => ['nullable', Rule::in(['phone', 'whatsapp', 'email'])],
            'preferred_contact_time' => ['nullable', Rule::in(['morning', 'afternoon', 'evening'])],
            'property_type' => ['nullable', Rule::in(['residential', 'apartment_multi_family', 'commercial', 'land_plot'])],
            'property_location' => ['nullable', 'string', 'max:255'],
            'number_of_units' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'current_status' => ['nullable', Rule::in(['occupied', 'vacant', 'under_construction'])],
            'service_type' => ['nullable', Rule::in(['full_service_management', 'tenant_finding_leasing', 'rent_collection_maintenance'])],
            'details' => ['nullable', 'string', 'max:2000'],
            'message' => ['nullable', 'string', 'max:2000'],
            'property_id' => ['nullable', 'integer', $this->publishedRule(Property::class)],
            'project_id' => ['nullable', 'integer', $this->publishedRule(Project::class)],
            'utm_source' => ['nullable', 'string', 'max:120'],
            'utm_medium' => ['nullable', 'string', 'max:120'],
            'utm_campaign' => ['nullable', 'string', 'max:120'],
            'landing_url' => ['nullable', 'string', 'max:500'],
            'referrer' => ['nullable', 'string', 'max:500'],
            'source' => ['nullable', 'string', 'max:50'],
            'contact_consent' => ['nullable'],
            'consent_given' => ['nullable'],
            'submission_token' => ['required', 'uuid'],
            'website' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'submission_token.*' => 'This form has expired. Refresh the page and try again.',
            'website.prohibited' => 'We could not accept this submission.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $data = $validator->getData();
                $required = $this->propertyManagementSubmission
                    ? ['full_name', 'email', 'property_type', 'property_location', 'service_type', 'contact_consent']
                    : ['name', 'consent_given'];

                foreach ($required as $field) {
                    $consent = in_array($data[$field] ?? null, ['yes', 'on', 1, '1', true, 'true'], true);

                    if (($field === 'contact_consent' || $field === 'consent_given') ? ! $consent : blank($data[$field] ?? null)) {
                        $validator->errors()->add($field, $field === 'contact_consent' || $field === 'consent_given'
                            ? 'Please agree to be contacted so we can reply.'
                            : 'This field is required.');
                    }
                }

                if (($data['preferred_contact'] ?? null) === 'email' || ($data['preferred_contact_method'] ?? null) === 'email') {
                    if (blank($data['email'] ?? null)) {
                        $validator->errors()->add('email', 'Add an email address if you would like us to reply by email.');
                    }
                }
            },
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): mixed
    {
        $data = parent::validated($key, $default);

        if ($key === null && is_array($data)) {
            unset($data['website']);
            $data['name'] = $data['full_name'] ?? $data['name'];
            $data['preferred_contact'] = $data['preferred_contact_method'] ?? $data['preferred_contact'] ?? null;
            $data['message'] = $data['details'] ?? $data['message'] ?? null;
            $data['consent_given'] = isset($data['contact_consent']) ? (bool) $data['contact_consent'] : (bool) ($data['consent_given'] ?? false);
            $data['type'] ??= null;
        }

        return $data;
    }

    /**
     * Inquiries may only reference records a visitor can actually see.
     *
     * @param  class-string<Property|Project>  $model
     */
    protected function publishedRule(string $model): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($model): void {
            if (! $model::query()->published()->whereKey((int) $value)->exists()) {
                $fail('That listing is no longer available. Please contact us directly.');
            }
        };
    }
}
