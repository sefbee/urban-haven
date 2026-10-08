<?php

namespace App\Http\Requests\Admin;

use App\Models\LocationArea;
use App\Models\Project;
use App\Support\SeoFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Stevebauman\Purify\Facades\Purify;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Project::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        foreach (['description', 'handover_info'] as $field) {
            if (is_string($this->input($field))) {
                $merge[$field] = Purify::clean($this->input($field));
            }
        }

        if ($this->has('slug')) {
            $merge['slug'] = SeoFields::normaliseSlug($this->input('slug'));
        }

        if ($this->filled('location_area_id') && ($area = LocationArea::query()->find($this->input('location_area_id')))) {
            $merge['city'] = $area->city;
        }

        $this->merge($merge);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $bounds = config('urbanhaven.maps.bounds');
        $project = $this->route('project');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => SeoFields::slugRules('projects', $project instanceof Project ? $project : null),
            'description' => ['nullable', 'string', 'max:50000'],
            'development_stage' => ['required', Rule::in(Project::STAGES)],
            'city' => ['required', 'string', 'max:120'],
            'location_area_id' => ['nullable', 'exists:location_areas,id'],
            'address' => ['nullable', 'string', 'max:255'],
            'lat' => ['nullable', 'required_with:lng', 'numeric', 'between:'.$bounds['south'].','.$bounds['north']],
            'lng' => ['nullable', 'required_with:lat', 'numeric', 'between:'.$bounds['west'].','.$bounds['east']],
            'video_url' => ['nullable', 'url:https', 'max:255', function (string $attribute, mixed $value, \Closure $fail): void {
                $host = strtolower((string) parse_url((string) $value, PHP_URL_HOST));

                if (! in_array($host, config('urbanhaven.media.allowed_video_hosts'), true)) {
                    $fail('Use a YouTube or Vimeo link.');
                }
            }],
            'developer_name' => ['nullable', 'string', 'max:255'],
            'completion_date' => ['nullable', 'date'],
            'handover_info' => ['nullable', 'string', 'max:10000'],
            'amenity_ids' => ['nullable', 'array'],
            'amenity_ids.*' => ['integer', 'exists:amenities,id'],
            'trust_label' => ['nullable', 'string', 'max:255'],
            'is_featured' => ['sometimes', 'boolean'],
            ...SeoFields::rules(),
            'photograph' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp', 'max:'.(int) config('urbanhaven.media.max_image_kb')],
            'photograph_alt' => ['nullable', 'string', 'max:200'],
        ];
    }
}
