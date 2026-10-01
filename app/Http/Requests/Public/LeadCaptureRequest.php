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
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->input('name')) ? trim($this->input('name')) : $this->input('name'),
            'email' => is_string($this->input('email')) ? trim($this->input('email')) : $this->input('email'),
            'phone' => is_string($this->input('phone')) ? trim($this->input('phone')) : $this->input('phone'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['nullable', Rule::in(array_diff(Lead::TYPES, ['visit_request']))],
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'phone' => ['required', 'string', 'max:24', new PhoneNumberRule],
            'email' => ['nullable', 'email:rfc', 'max:190'],
            'preferred_contact' => ['nullable', Rule::in(['phone', 'whatsapp', 'email'])],
            'message' => ['nullable', 'string', 'max:2000'],
            'property_id' => ['nullable', 'integer', $this->publishedRule(Property::class)],
            'project_id' => ['nullable', 'integer', $this->publishedRule(Project::class)],
            'utm_source' => ['nullable', 'string', 'max:120'],
            'utm_medium' => ['nullable', 'string', 'max:120'],
            'utm_campaign' => ['nullable', 'string', 'max:120'],
            'landing_url' => ['nullable', 'string', 'max:500'],
            'referrer' => ['nullable', 'string', 'max:500'],
            'source' => ['nullable', 'string', 'max:50'],
            'consent_given' => ['accepted'],
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
            'consent_given.accepted' => 'Please agree to be contacted so we can reply.',
            'submission_token.*' => 'This form has expired. Refresh the page and try again.',
            'website.prohibited' => 'We could not accept this submission.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (($this->input('preferred_contact') === 'email') && blank($this->input('email'))) {
                    $validator->errors()->add('email', 'Add an email address if you would like us to reply by email.');
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
            $data['consent_given'] = true;
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
