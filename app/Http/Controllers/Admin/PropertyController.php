<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\InventoryService;
use App\Contracts\MediaService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePropertyRequest;
use App\Http\Requests\Admin\UpdatePropertyRequest;
use App\Models\Amenity;
use App\Models\LocationArea;
use App\Models\Project;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\PublicationState;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PropertyController extends Controller
{
    private const SEO_FIELDS = ['meta_title', 'meta_description', 'noindex'];

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Property::class);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(array_keys(PublicationState::LABELS))],
            'availability' => ['nullable', Rule::in(Property::AVAILABILITIES)],
            'listing_type' => ['nullable', 'in:sale,rent'],
            'location_area_id' => ['nullable', 'integer'],
            'project_id' => ['nullable', 'integer'],
        ]);

        $query = Property::query()
            ->with(['propertyType', 'locationArea', 'publicationState', 'project:id,name'])
            ->when($filters['q'] ?? null, function ($query, string $q): void {
                $term = '%'.addcslashes($q, '%_\\').'%';
                $query->where(fn ($builder) => $builder->where('title', 'like', $term)->orWhere('reference', 'like', $term)->orWhere('slug', 'like', $term));
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $status === PublicationState::DRAFT
                ? $query->where(fn ($builder) => $builder->whereDoesntHave('publicationState')->orWhereHas('publicationState', fn ($state) => $state->where('status', $status)))
                : $query->whereHas('publicationState', fn ($state) => $state->where('status', $status)))
            ->when($filters['availability'] ?? null, fn ($query, string $availability) => $query->where('availability', $availability))
            ->when($filters['listing_type'] ?? null, fn ($query, string $type) => $query->where('listing_type', $type))
            ->when($filters['location_area_id'] ?? null, fn ($query, int|string $area) => $query->where('location_area_id', $area))
            ->when($filters['project_id'] ?? null, fn ($query, int|string $project) => $query->where('project_id', $project))
            ->latest('updated_at');

        return view('admin.properties.index', [
            'properties' => $query->paginate(20)->withQueryString(),
            'filters' => $filters,
            'q' => $filters['q'] ?? '',
            'areas' => LocationArea::query()->orderBy('name')->get(['id', 'name']),
            'projects' => Project::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Property::class);

        return view('admin.properties.create', $this->formData(new Property([
            'listing_type' => 'sale',
            'availability' => 'available',
            'price_mode' => Property::PRICE_FIXED,
            'area_unit' => 'sqft',
        ])));
    }

    public function store(StorePropertyRequest $request, InventoryService $inventory, MediaService $media): RedirectResponse
    {
        $property = $inventory->createProperty($request->safe()->except([...self::SEO_FIELDS, 'photograph', 'photograph_alt']), $request->user());
        $this->syncSeo($property, $request->validated());

        if ($request->hasFile('photograph')) {
            $media->store($property, $request->file('photograph'), 'gallery', $request->validated('photograph_alt'));
        }

        return redirect()->route('admin.properties.edit', $property)->with('status', 'Property saved as a draft with reference '.$property->reference.'.');
    }

    public function edit(Property $property, InventoryService $inventory): View
    {
        $this->authorize('view', $property);
        $property->load(['publicationState', 'seoOverride', 'media', 'units', 'statusHistory.actor:id,name', 'propertyType']);

        return view('admin.properties.edit', [
            ...$this->formData($property),
            'checklist' => $inventory->publishChecklist($property),
            'canEdit' => request()->user()->can('update', $property),
        ]);
    }

    public function update(UpdatePropertyRequest $request, Property $property, InventoryService $inventory): RedirectResponse
    {
        $inventory->updateProperty($property, $request->safe()->except([...self::SEO_FIELDS, 'photograph', 'photograph_alt']), $request->user());
        $this->syncSeo($property, $request->validated());

        return redirect()->route('admin.properties.edit', $property->fresh())->with('status', 'Property updated.');
    }

    public function updateAvailability(Request $request, Property $property, InventoryService $inventory): RedirectResponse
    {
        $this->authorize('updateAvailability', $property);

        $validated = $request->validate([
            'availability' => ['required', Rule::in(Property::AVAILABILITIES)],
            'version' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:255'],
            'reservation_expires_at' => ['nullable', 'date', 'after:now', 'before:+1 year'],
        ]);

        $expiresAt = $validated['availability'] === 'reserved'
            ? ($validated['reservation_expires_at'] ?? now()->addDays((int) config('urbanhaven.inventory.reservation_days')))
            : null;

        $inventory->updateAvailability($property, $validated['availability'], (int) $validated['version'], $request->user(), $validated['note'] ?? null, $expiresAt);

        return back()->with('status', 'Availability changed to '.Property::AVAILABILITY_LABELS[$validated['availability']].'.');
    }

    public function destroy(Property $property, InventoryService $inventory): RedirectResponse
    {
        $this->authorize('delete', $property);
        $inventory->deleteProperty($property, request()->user());

        return redirect()->route('admin.properties.index')->with('status', 'Property deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Property $property): array
    {
        return [
            'property' => $property,
            'projects' => Project::query()->orderBy('name')->get(['id', 'name']),
            'types' => PropertyType::query()->active()->orderBy('label')->get(),
            'areas' => LocationArea::query()->active()->orderBy('name')->get(),
            'amenities' => Amenity::query()->active()->orderBy('label')->get(),
            'contacts' => User::query()->salesStaff()->orderBy('name')->get(['id', 'name']),
            'canEditReference' => $property->exists
                ? request()->user()->can('editReference', $property)
                : request()->user()->hasPermission('property.reference'),
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function syncSeo(Property $property, array $validated): void
    {
        $property->seoOverride()->updateOrCreate([], [
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
            'noindex' => (bool) ($validated['noindex'] ?? false),
            'updated_at' => now(),
        ]);
    }
}
