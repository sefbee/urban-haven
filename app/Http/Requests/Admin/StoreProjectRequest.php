<?php

namespace App\Http\Requests\Admin;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Project::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'development_stage' => ['required', 'in:upcoming,ongoing,completed'],
            'city' => ['required', 'string', 'max:120'],
            'location_area_id' => ['nullable', 'exists:location_areas,id'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
            'video_url' => ['nullable', 'url:https', 'max:255', function (string $attribute, mixed $value, \Closure $fail): void {
                $host = strtolower((string) parse_url((string) $value, PHP_URL_HOST));

                if (! in_array($host, config('urbanhaven.media.allowed_video_hosts'), true)) {
                    $fail('Use a YouTube or Vimeo link.');
                }
            }],
            'developer_name' => ['nullable', 'string', 'max:255'],
            'completion_date' => ['nullable', 'date'],
            'handover_info' => ['nullable', 'string'],
            'amenity_ids' => ['nullable', 'array'],
            'trust_label' => ['nullable', 'string', 'max:255'],
            'is_featured' => ['sometimes', 'boolean'],
            'photograph' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp', 'max:'.(int) config('urbanhaven.media.max_image_kb')],
            'photograph_alt' => ['nullable', 'string', 'max:200'],
        ];
    }
}
