<?php

namespace App\Http\Requests\Public;

use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Setting;
use App\Support\SearchBands;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'listing_type' => ['nullable', Rule::in(Setting::enabledPurposes())],
            'category' => ['nullable', Rule::in(PropertyType::CATEGORIES)],
            'property_type_id' => ['nullable', 'integer', 'exists:property_types,id'],
            'property_type_ids' => ['nullable', 'array', 'max:40'],
            'property_type_ids.*' => ['integer', 'distinct', 'exists:property_types,id'],
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
            'availability' => ['nullable', Rule::in(Property::AVAILABILITIES)],
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
            'view' => ['nullable', Rule::in(['list', 'grid', 'map'])],
            'sort' => ['nullable', 'in:newest,price_asc,price_desc,area_desc,beds_desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:24'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'listing_type.in' => 'That purpose is not available.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                foreach ([
                    'price' => 'The maximum price must be at least the minimum price.',
                    'beds' => 'The maximum bedrooms must be at least the minimum.',
                    'road_width' => 'The maximum road width must be at least the minimum.',
                ] as $field => $message) {
                    $min = $this->input('min_'.$field);
                    $max = $this->input('max_'.$field);

                    if (is_numeric($min) && is_numeric($max) && (float) $max < (float) $min) {
                        $validator->errors()->add('max_'.$field, $message);
                    }
                }
            },
        ];
    }

    /**
     * Invalid filters never produce an error page: the visitor lands on the same search with the bad values dropped.
     */
    protected function failedValidation(ValidatorContract $validator): void
    {
        if ($this->expectsJson()) {
            parent::failedValidation($validator);
        }

        $invalid = collect($validator->errors()->keys())
            ->map(fn (string $key): string => explode('.', $key)[0])
            ->unique()
            ->all();

        $valid = collect($this->query())->except($invalid)->all();

        throw new HttpResponseException(
            redirect()->route('properties.index', $valid)->withErrors($validator, 'search'),
        );
    }

    protected function prepareForValidation(): void
    {
        if ($this->exists('q')) {
            $this->merge(['q' => trim((string) $this->input('q'))]);
        }
    }
}
