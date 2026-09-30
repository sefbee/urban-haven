<?php

namespace App\Http\Requests\Admin;

use App\Models\Property;
use Illuminate\Foundation\Http\FormRequest;

class StorePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Property::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'property_type_id' => ['required', 'exists:property_types,id'],
            'location_area_id' => ['required', 'exists:location_areas,id'],
            'listing_type' => ['required', 'in:sale,rent'],
            'availability' => ['required', 'in:available,reserved,sold,rented,off-market'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'price_basis' => ['required', 'in:total_sale,monthly_rent'],
            'area_value' => ['nullable', 'numeric', 'min:0'],
            'area_unit' => ['required', 'string'],
            'bedrooms' => ['nullable', 'integer', 'min:0'],
            'bathrooms' => ['nullable', 'integer', 'min:0'],
            'floor_number' => ['nullable', 'integer'],
            'is_furnished' => ['sometimes', 'boolean'],
            'amenity_ids' => ['nullable', 'array'],
            'amenity_ids.*' => ['integer', 'exists:amenities,id'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
            'video_url' => ['nullable', 'url'],
            'virtual_tour_url' => ['nullable', 'url'],
            'trust_label' => ['nullable', 'string', 'max:255'],
            'is_featured' => ['sometimes', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:160'],
            'noindex' => ['sometimes', 'boolean'],
        ];
    }
}
