<?php

namespace App\Http\Requests\Admin;

use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;
use App\Support\SeoFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Stevebauman\Purify\Facades\Purify;

class StorePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Property::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        if (is_string($this->input('description'))) {
            $merge['description'] = Purify::clean($this->input('description'));
        }

        if (! $this->filled('price_basis') && $this->filled('listing_type')) {
            $merge['price_basis'] = $this->input('listing_type') === 'rent' ? 'monthly_rent' : 'total_sale';
        }

        if ($this->input('price_mode') === Property::PRICE_ON_REQUEST) {
            $merge['price'] = null;
        }

        if ($this->filled('reference')) {
            $merge['reference'] = strtoupper(trim((string) $this->input('reference')));
        }

        if ($this->has('slug')) {
            $merge['slug'] = SeoFields::normaliseSlug($this->input('slug'));
        }

        $this->merge($merge);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $bounds = config('urbanhaven.maps.bounds');
        $property = $this->route('property');
        $canEditReference = $property instanceof Property
            ? $this->user()?->can('editReference', $property)
            : $this->user()?->hasPermission('property.reference');

        return [
            'version' => [$property instanceof Property ? 'required' : 'nullable', 'integer', 'min:1'],
            'title' => ['required', 'string', 'max:255'],
            'reference' => $canEditReference
                ? ['nullable', 'string', 'max:30', 'regex:/^[A-Z0-9-]+$/', Rule::unique('properties', 'reference')->ignore($property instanceof Property ? $property->id : null)]
                : ['prohibited'],
            'description' => ['nullable', 'string', 'max:50000'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'property_type_id' => ['required', Rule::exists('property_types', 'id')->where('is_active', true)],
            'location_area_id' => ['required', Rule::exists('location_areas', 'id')->where('is_active', true)],
            'address' => ['nullable', 'string', 'max:255'],
            'listing_type' => ['required', 'in:sale,rent'],
            'availability' => ['required', Rule::in(Property::AVAILABILITIES)],
            'price_mode' => ['required', Rule::in([Property::PRICE_FIXED, Property::PRICE_ON_REQUEST])],
            'price' => ['nullable', 'required_if:price_mode,'.Property::PRICE_FIXED, 'numeric', 'min:1', 'max:99999999999'],
            'price_basis' => ['required', 'in:total_sale,monthly_rent'],
            'area_value' => ['required', 'numeric', 'min:1', 'max:100000000'],
            'area_unit' => ['required', Rule::in(array_keys(config('urbanhaven.area_units')))],
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'bathrooms' => ['nullable', 'integer', 'min:0', 'max:50'],
            'balconies' => ['nullable', 'integer', 'min:0', 'max:50'],
            'parking_spaces' => ['nullable', 'integer', 'min:0', 'max:500'],
            'floor_number' => ['nullable', 'integer', 'min:-5', 'max:200'],
            'is_furnished' => ['sometimes', 'boolean'],
            'facing' => ['nullable', Rule::in(array_keys(config('urbanhaven.facings', [])))],
            'road_width_ft' => ['nullable', 'integer', 'min:0', 'max:200'],
            'amenity_ids' => ['nullable', 'array'],
            'amenity_ids.*' => ['integer', 'exists:amenities,id'],
            'lat' => ['nullable', 'required_with:lng', 'numeric', 'between:'.$bounds['south'].','.$bounds['north']],
            'lng' => ['nullable', 'required_with:lat', 'numeric', 'between:'.$bounds['west'].','.$bounds['east']],
            'video_url' => ['nullable', 'url:https', 'max:255', function (string $attribute, mixed $value, \Closure $fail): void {
                $host = strtolower((string) parse_url((string) $value, PHP_URL_HOST));

                if (! in_array($host, config('urbanhaven.media.allowed_video_hosts'), true)) {
                    $fail('Use a YouTube or Vimeo link.');
                }
            }],
            'virtual_tour_url' => ['nullable', 'url:https', 'max:255'],
            'trust_label' => ['nullable', 'string', 'max:120'],
            'assigned_contact_id' => ['nullable', Rule::exists('users', 'id')->where('is_active', true)],
            'display_priority' => ['nullable', 'integer', 'min:1', 'max:999'],
            'is_featured' => ['sometimes', 'boolean'],
            'slug' => SeoFields::slugRules('properties', $property instanceof Property ? $property : null),
            ...SeoFields::rules(),
            'photograph' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp', 'max:'.(int) config('urbanhaven.media.max_image_kb')],
            'photograph_alt' => ['nullable', 'string', 'max:200'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $type = PropertyType::query()->find($this->input('property_type_id'));

                if ($type && ! $type->hasResidentialFields()) {
                    foreach (['bedrooms', 'bathrooms', 'balconies', 'floor_number'] as $field) {
                        if (filled($this->input($field))) {
                            $validator->errors()->add($field, 'Land and plots do not have '.str_replace('_', ' ', $field).'. Leave this blank.');
                        }
                    }
                }

                $expectedBasis = $this->input('listing_type') === 'rent' ? 'monthly_rent' : 'total_sale';

                if ($this->filled('listing_type') && $this->input('price_basis') !== $expectedBasis) {
                    $validator->errors()->add('price_basis', 'Sale listings use a total price and rentals use a monthly rent.');
                }

                if ($this->filled('assigned_contact_id')) {
                    $contact = User::query()->find($this->input('assigned_contact_id'));

                    if ($contact && ! $contact->hasPermission('lead.view')) {
                        $validator->errors()->add('assigned_contact_id', 'The listing contact must be a sales or owner account.');
                    }
                }
            },
        ];
    }
}
