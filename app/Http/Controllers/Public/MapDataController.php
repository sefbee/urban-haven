<?php

namespace App\Http\Controllers\Public;

use App\Contracts\SearchService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\SearchRequest;
use App\Models\Property;
use App\Models\PropertyType;
use App\Support\AreaConverter;
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

                if ($coordinates === null) {
                    return null;
                }

                $residential = $property->fieldProfile() !== PropertyType::PROFILE_PLOT;
                $image = $property->featuredImage()?->url(480);
                $place = collect([$property->locationArea?->name, $property->locationArea?->city])->filter()->implode(', ');
                $compactPrice = MoneyFormatter::compactBdt($property->price);
                $previewPrice = $property->isPriceOnRequest()
                    ? __('Price on request')
                    : ($property->listing_type !== 'rent' && $compactPrice !== null ? 'BDT '.$compactPrice : MoneyFormatter::formatBdt($property->price, $property->price_basis));
                $specs = array_values(array_filter([
                    $residential && $property->bedrooms ? $property->bedrooms.' '.__('bed') : null,
                    $residential && $property->bathrooms ? $property->bathrooms.' '.__('bath') : null,
                    $property->area_value ? AreaConverter::format($property->area_value, $property->area_unit) : null,
                    $residential && $property->is_furnished ? __('Furnished') : null,
                ]));

                return [
                    'id' => $property->id,
                    'title' => $property->title,
                    'url' => route('properties.show', $property->slug),
                    'price' => $property->isPriceOnRequest() ? __('Price on request') : MoneyFormatter::formatBdt($property->price, $property->price_basis),
                    'lat' => $coordinates['lat'],
                    'lng' => $coordinates['lng'],
                    'approximate' => $coordinates['approximate'],
                    'image' => $image,
                    'place' => $place,
                    'quickViewLabel' => __('Quick view'),
                    'area' => $property->area_value ? AreaConverter::format($property->area_value, $property->area_unit) : null,
                    'beds' => $residential ? $property->bedrooms : null,
                    'baths' => $residential ? $property->bathrooms : null,
                    'furnishing' => $residential && $property->is_furnished !== null ? ($property->is_furnished ? __('Furnished') : __('Unfurnished')) : null,
                    'preview' => [
                        'id' => $property->id,
                        'title' => $property->title,
                        'url' => route('properties.show', $property->slug),
                        'price' => $previewPrice,
                        'location' => $place,
                        'listing' => $property->listing_type === 'rent' ? __('For rent') : __('For sale'),
                        'type' => $property->propertyType?->label,
                        'specs' => $specs,
                        'images' => $image ? [['url' => $image, 'alt' => $property->title]] : [],
                    ],
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
     * @return list<array<string, mixed>>
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

        return array_values(array_map(fn (array $cell): array => count($cell['lat']) === 1 ? [...$cell['first'], 'count' => 1] : [
            'lat' => round(array_sum($cell['lat']) / count($cell['lat']), 5),
            'lng' => round(array_sum($cell['lng']) / count($cell['lng']), 5),
            'count' => count($cell['lat']),
            'url' => count($cell['lat']) === 1 ? $cell['first']['url'] : null,
            'title' => count($cell['lat']) === 1 ? $cell['first']['title'] : null,
        ], $cells));
    }
}
