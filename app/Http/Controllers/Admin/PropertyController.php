<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\InventoryService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePropertyRequest;
use App\Http\Requests\Admin\UpdatePropertyRequest;
use App\Models\Amenity;
use App\Models\LocationArea;
use App\Models\Project;
use App\Models\Property;
use App\Models\PropertyType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PropertyController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Property::class);

        $query = Property::query()->with(['propertyType', 'locationArea', 'publicationState'])->latest('id');

        if (filled($request->string('q'))) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($builder) use ($term): void {
                $builder->where('title', 'like', $term)
                    ->orWhere('reference', 'like', $term)
                    ->orWhere('slug', 'like', $term);
            });
        }

        return view('admin.properties.index', [
            'properties' => $query->paginate(20)->withQueryString(),
            'q' => $request->string('q')->toString(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Property::class);

        return view('admin.properties.create', $this->formData());
    }

    public function store(StorePropertyRequest $request, InventoryService $inventory): RedirectResponse
    {
        $property = $inventory->createProperty($request->safe()->except(['meta_title', 'meta_description', 'noindex']), $request->user());
        $this->syncSeo($property, $request->validated());

        return redirect()->route('admin.properties.edit', $property)->with('status', 'Property created.');
    }

    public function edit(Property $property): View
    {
        $this->authorize('update', $property);

        return view('admin.properties.edit', array_merge($this->formData(), [
            'property' => $property->load(['publicationState', 'seoOverride', 'media', 'units']),
        ]));
    }

    public function update(UpdatePropertyRequest $request, Property $property, InventoryService $inventory): RedirectResponse
    {
        $inventory->updateProperty($property, $request->safe()->except(['meta_title', 'meta_description', 'noindex']), $request->user());
        $this->syncSeo($property, $request->validated());

        return redirect()->route('admin.properties.edit', $property)->with('status', 'Property updated.');
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
    private function formData(): array
    {
        return [
            'projects' => Project::query()->orderBy('name')->get(),
            'types' => PropertyType::query()->active()->orderBy('label')->get(),
            'areas' => LocationArea::query()->active()->orderBy('name')->get(),
            'amenities' => Amenity::query()->active()->orderBy('label')->get(),
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
