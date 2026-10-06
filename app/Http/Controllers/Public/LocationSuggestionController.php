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

    /**
     * Area suggestions for the search fields, most-listed first, in the shape Select2 reads.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
        ]);
        $term = trim((string) ($validated['q'] ?? ''));
        $purposes = Setting::enabledPurposes() ?: ['__none__'];

        $areas = LocationArea::query()
            ->active()
            ->when($term !== '', fn (Builder $query) => $query->where(fn (Builder $match) => $match
                ->where('name', 'like', '%'.addcslashes($term, '%_\\').'%')
                ->orWhere('city', 'like', '%'.addcslashes($term, '%_\\').'%')))
            ->withCount(['properties' => fn (Builder $query) => $query->published()
                ->whereIn('listing_type', $purposes)
                ->whereNotIn('availability', Property::UNAVAILABLE)])
            ->orderByDesc('properties_count')
            ->orderBy('name')
            ->limit(self::LIMIT)
            ->get(['id', 'name', 'city']);

        return response()->json([
            'results' => $areas->map(fn (LocationArea $area): array => [
                'id' => $area->id,
                'text' => $area->name,
                'city' => $area->city,
                'count' => $area->properties_count,
                'meta' => trans_choice(':count home|:count homes', $area->properties_count, ['count' => $area->properties_count]),
            ])->values(),
        ])->header('Cache-Control', 'public, max-age=120');
    }
}
