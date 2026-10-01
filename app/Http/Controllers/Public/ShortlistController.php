<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Support\SeoMeta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Shortlist, compare and recently viewed live in the visitor's browser (versioned localStorage);
 * the server only renders cards for IDs that are still published, so stale entries can be dropped.
 */
class ShortlistController extends Controller
{
    private const MAX_IDS = 24;

    public function shortlist(): View
    {
        return view('public.shortlist', [
            'seo' => SeoMeta::for(null, 'Your shortlist', null, ['noindex' => true]),
        ]);
    }

    public function compare(): View
    {
        return view('public.compare', [
            'compareLimit' => (int) config('urbanhaven.search.compare_limit', 4),
            'seo' => SeoMeta::for(null, 'Compare properties', null, ['noindex' => true]),
        ]);
    }

    public function cards(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['nullable', 'string', 'max:300', 'regex:/^\d{1,10}(,\d{1,10}){0,'.(self::MAX_IDS - 1).'}$/'],
            'view' => ['nullable', 'in:card,compare,strip'],
        ]);

        $ids = collect(explode(',', (string) ($validated['ids'] ?? '')))
            ->map(fn (string $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->take(self::MAX_IDS)
            ->values();

        if ($ids->isEmpty()) {
            return response()->json(['ids' => [], 'html' => '']);
        }

        $properties = Property::query()
            ->published()
            ->whereKey($ids->all())
            ->with(['propertyType', 'locationArea', 'media'])
            ->get()
            ->sortBy(fn (Property $property): int|false => $ids->search($property->id))
            ->values();

        $view = $validated['view'] ?? 'card';

        return response()->json([
            'ids' => $properties->pluck('id')->all(),
            'html' => view('public.partials.saved-'.$view, ['properties' => $properties])->render(),
        ])->header('Cache-Control', 'private, max-age=60');
    }
}
