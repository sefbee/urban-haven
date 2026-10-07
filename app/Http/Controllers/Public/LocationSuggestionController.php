<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\LocationArea;
use App\Models\Property;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationSuggestionController extends Controller
{
    private const LIMIT = 10;

    private const PROPERTY_LIMIT = 4;

    /**
     * Area suggestions for the search fields, most-listed first, in the shape Select2 reads.
     * With include=properties, matching live listings are returned alongside for the homepage search.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'include' => ['nullable', 'in:properties'],
        ]);
        $term = trim((string) ($validated['q'] ?? ''));
        $purposes = Setting::enabledPurposes() ?: ['__none__'];

        $areas = LocationArea::query()
            ->active()
            ->when($term !== '', fn (Builder $query) => $query->where(fn (Builder $match) => $match
                ->where('name', 'like', $this->like($term))
                ->orWhere('city', 'like', $this->like($term))))
            ->withCount(['properties' => fn (Builder $query) => $query->published()
                ->whereIn('listing_type', $purposes)
                ->whereNotIn('availability', Property::UNAVAILABLE)])
            ->orderByDesc('properties_count')
            ->orderBy('name')
            ->limit(self::LIMIT)
            ->get(['id', 'name', 'city']);

        $payload = [
            'results' => $areas->map(fn (LocationArea $area): array => [
                'id' => $area->id,
                'text' => $area->name,
                'city' => $area->city,
                'count' => $area->properties_count,
                'meta' => trans_choice(':count home|:count homes', $area->properties_count, ['count' => $area->properties_count]),
            ])->values(),
        ];

        if (($validated['include'] ?? null) === 'properties') {
            $payload['properties'] = $this->properties($term, $purposes);
        }

        return response()->json($payload)->header('Cache-Control', 'public, max-age=120');
    }

    /**
     * Live listings whose title or reference matches, or the featured and newest ones when nothing is typed.
     *
     * @param  list<string>  $purposes
     * @return list<array{title: string, url: string, place: string}>
     */
    private function properties(string $term, array $purposes): array
    {
        return Property::query()
            ->published()
            ->whereIn('listing_type', $purposes)
            ->whereNotIn('availability', Property::UNAVAILABLE)
            ->when($term !== '', fn (Builder $query) => $query->where(fn (Builder $match) => $match
                ->where('title', 'like', $this->like($term))
                ->orWhere('reference', 'like', $this->like($term))))
            ->with('locationArea:id,name,city')
            ->orderByDesc('is_featured')
            ->latest('id')
            ->limit(self::PROPERTY_LIMIT)
            ->get(['id', 'title', 'slug', 'location_area_id'])
            ->map(fn (Property $property): array => [
                'title' => $property->title,
                'url' => route('properties.show', $property->slug),
                'place' => collect([$property->locationArea?->name, $property->locationArea?->city])->filter()->implode(', '),
            ])
            ->all();
    }

    private function like(string $term): string
    {
        return '%'.addcslashes($term, '%_\\').'%';
    }
}
