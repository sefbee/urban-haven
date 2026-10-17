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
use App\Models\User;
use App\Support\SeoFields;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class PropertyController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Property::class);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
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

        return view('admin.properties.create', [
            ...$this->formData(new Property([
                'listing_type' => 'sale',
                'availability' => 'available',
                'price_mode' => Property::PRICE_FIXED,
                'area_unit' => 'sqft',
            ])),
        ]);
    }

    public function store(StorePropertyRequest $request, InventoryService $inventory, MediaService $media): RedirectResponse
    {
        $storedMedia = [];

        try {
            $property = DB::transaction(function () use ($request, $inventory, $media, &$storedMedia): Property {
                $property = $inventory->createProperty($this->attributes($request), $request->user());
                SeoFields::sync($property, $request->validated());

                if ($request->hasFile('photograph')) {
                    $cover = $media->store($property, $request->file('photograph'), 'gallery', $request->validated('photograph_alt'));
                    $storedMedia[] = $cover;
                    $property->forceFill(['featured_media_id' => $cover->id])->save();
                }

                foreach ($request->file('photographs', []) as $index => $file) {
                    $storedMedia[] = $media->store($property, $file, 'gallery', $request->validated("photographs_alt.{$index}"));
                }

                $media->completePropertyAltText($property);

                return $property;
            });
        } catch (Throwable $exception) {
            foreach ($storedMedia as $storedImage) {
                $media->cleanupDeletedFiles($storedImage);
            }

            throw $exception;
        }

        return redirect()->route('admin.properties.edit', $property)->with('status', 'Property published with reference '.$property->reference.'.');
    }

    public function edit(Property $property): View
    {
        $this->authorize('view', $property);
        $property->load(['publicationState', 'seoOverride', 'media', 'units', 'statusHistory.actor:id,name', 'propertyType']);

        $user = request()->user();

        return view('admin.properties.edit', [
            ...$this->formData($property),
            'canEdit' => $user->can('update', $property),
        ]);
    }

    public function update(UpdatePropertyRequest $request, Property $property, InventoryService $inventory, MediaService $media): RedirectResponse
    {
        $inventory->updateProperty($property, $this->attributes($request), $request->user());
        $media->completePropertyAltText($property);
        SeoFields::sync($property, $request->validated());

        return redirect()->route('admin.properties.edit', $property->fresh())->with('status', 'Property published and updated.');
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

        try {
            $inventory->deleteProperty($property, request()->user());
        } catch (QueryException $exception) {
            report($exception);

            return back()->withErrors(['delete' => 'The property could not be deleted. No changes were made. Please try again or contact an administrator.']);
        }

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
     * Listing columns only; a blank permalink keeps the current one (or is generated from the title).
     *
     * @return array<string, mixed>
     */
    private function attributes(StorePropertyRequest $request): array
    {
        $attributes = $request->safe()->except([...SeoFields::inputNames(), 'photograph', 'photograph_alt', 'photographs', 'photographs_alt']);

        if (blank($attributes['slug'] ?? null)) {
            unset($attributes['slug']);
        }

        return $attributes;
    }
}
