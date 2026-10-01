<?php

namespace App\Http\Requests\Public;

use App\Support\SearchBands;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchRequest extends FormRequest
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
            'q' => ['nullable', 'string', 'max:120'],
            'listing_type' => ['nullable', 'in:sale,rent'],
            'property_type_id' => ['nullable', 'integer', 'exists:property_types,id'],
            'location_area_id' => ['nullable', 'integer', 'exists:location_areas,id'],
            'location_area_ids' => ['nullable', 'array', 'max:8'],
            'location_area_ids.*' => ['integer', 'distinct', 'exists:location_areas,id'],
            'city' => ['nullable', 'string', 'max:120'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'price_band' => ['nullable', 'string', Rule::in(array_keys(SearchBands::prices()))],
            'area_band' => ['nullable', 'string', Rule::in(array_keys(SearchBands::areas()))],
            'min_beds' => ['nullable', 'integer', 'min:0', 'max:20'],
            'max_beds' => ['nullable', 'integer', 'min:0', 'max:20'],
            'min_baths' => ['nullable', 'integer', 'min:0', 'max:20'],
            'availability' => ['nullable', 'string'],
            'is_furnished' => ['nullable', 'boolean'],
            'furnishing' => ['nullable', 'array', 'max:3'],
            'furnishing.*' => ['in:full,semi,unfurnished'],
            'is_verified' => ['nullable', 'boolean'],
            'verification' => ['nullable', 'array', 'max:2'],
            'verification.*' => ['in:verified,unverified'],
            'facing' => ['nullable', Rule::in(array_keys(config('urbanhaven.facings', [])))],
            'min_road_width' => ['nullable', 'integer', 'min:0', 'max:200'],
            'max_road_width' => ['nullable', 'integer', 'min:0', 'max:200'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'radius_km' => ['nullable', 'numeric', 'min:0.5', 'max:25'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['integer'],
            'sort' => ['nullable', 'in:newest,price_asc,price_desc,area_desc,beds_desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:24'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->exists('q')) {
            $this->merge(['q' => trim((string) $this->input('q'))]);
        }
    }
}
