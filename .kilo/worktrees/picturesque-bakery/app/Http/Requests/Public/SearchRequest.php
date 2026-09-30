<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

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
            'listing_type' => ['nullable', 'in:sale,rent'],
            'property_type_id' => ['nullable', 'integer', 'exists:property_types,id'],
            'location_area_id' => ['nullable', 'integer', 'exists:location_areas,id'],
            'city' => ['nullable', 'string', 'max:120'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'min_beds' => ['nullable', 'integer', 'min:0'],
            'max_beds' => ['nullable', 'integer', 'min:0'],
            'availability' => ['nullable', 'string'],
            'is_furnished' => ['nullable', 'boolean'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['integer'],
            'sort' => ['nullable', 'in:newest,price_asc,price_desc,area_desc,beds_desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:24'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
