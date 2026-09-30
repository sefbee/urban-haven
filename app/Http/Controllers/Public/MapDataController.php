<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Support\MoneyFormatter;
use Illuminate\Http\JsonResponse;

class MapDataController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $decimals = (int) config('urbanhaven.maps.approximate_decimals', 2);

        $properties = Property::query()
            ->published()
            ->whereNotNull('lat')
            ->get(['id', 'title', 'slug', 'lat', 'lng', 'price', 'price_basis'])
            ->map(fn (Property $property) => [
                'id' => $property->id,
                'title' => $property->title,
                'url' => route('properties.show', $property->slug),
                'price' => MoneyFormatter::formatBdt($property->price, $property->price_basis),
                'lat' => round((float) $property->lat, $decimals),
                'lng' => round((float) $property->lng, $decimals),
            ]);

        return response()->json(['properties' => $properties]);
    }
}
