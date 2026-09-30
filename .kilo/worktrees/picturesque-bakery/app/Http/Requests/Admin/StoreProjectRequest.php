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
            'developer_name' => ['nullable', 'string', 'max:255'],
            'completion_date' => ['nullable', 'date'],
            'handover_info' => ['nullable', 'string'],
            'amenity_ids' => ['nullable', 'array'],
            'trust_label' => ['nullable', 'string', 'max:255'],
            'is_featured' => ['sometimes', 'boolean'],
        ];
    }
}
