<?php

namespace App\Http\Controllers\Public;

use App\Contracts\SearchService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\SearchRequest;
use App\Models\Property;
use App\Support\MoneyFormatter;
use Illuminate\Http\JsonResponse;

class MapDataController extends Controller
{
    /**
     * Pins for the same filters as the list view. Above the threshold, nearby pins are grouped
     * into grid clusters server-side so the browser never receives an unbounded payload.
     */
    public function __invoke(SearchRequest $request, SearchService $search): JsonResponse
    {
        $filters = $request->validated();
        $filters['location_area_ids'] = array_values(array_filter(array_map('intval', [
            ...(array) ($filters['location_area_ids'] ?? []),
            $filters['location_area_id'] ?? 0,
        ])));

        $points = $search->mapListings($filters, (int) config('urbanhaven.maps.max_points', 500))
            ->map(function (Property $property): ?array {
                $coordinates = $property->publicCoordinates();

                return $coordinates === null ? null : [
                    'id' => $property->id,
                    'title' => $property->title,
                    'url' => route('properties.show', $property->slug),
                    'price' => $property->isPriceOnRequest() ? __('Price on request') : MoneyFormatter::formatBdt($property->price, $property->price_basis),
                    'lat' => $coordinates['lat'],
                    'lng' => $coordinates['lng'],
                    'approximate' => $coordinates['approximate'],
                ];
            })
            ->filter()
            ->values();

        $threshold = (int) config('urbanhaven.maps.cluster_threshold', 60);

        return response()->json([
            'total' => $points->count(),
            'clustered' => $points->count() > $threshold,
            'points' => $points->count() > $threshold ? [] : $points->all(),
            'clusters' => $points->count() > $threshold ? $this->cluster($points->all()) : [],
        ])->header('Cache-Control', 'public, max-age=120');
    }

    /**
     * @param  list<array{lat: float, lng: float, url: string, title: string}>  $points
     * @return list<array{lat: float, lng: float, count: int, url: ?string, title: ?string}>
     */
    private function cluster(array $points): array
    {
        $cells = [];
        $size = 0.02;

        foreach ($points as $point) {
            $key = floor($point['lat'] / $size).':'.floor($point['lng'] / $size);
            $cells[$key]['lat'][] = $point['lat'];
            $cells[$key]['lng'][] = $point['lng'];
            $cells[$key]['first'] ??= $point;
        }

        return array_values(array_map(fn (array $cell): array => [
            'lat' => round(array_sum($cell['lat']) / count($cell['lat']), 5),
            'lng' => round(array_sum($cell['lng']) / count($cell['lng']), 5),
            'count' => count($cell['lat']),
            'url' => count($cell['lat']) === 1 ? $cell['first']['url'] : null,
            'title' => count($cell['lat']) === 1 ? $cell['first']['title'] : null,
        ], $cells));
    }
}
