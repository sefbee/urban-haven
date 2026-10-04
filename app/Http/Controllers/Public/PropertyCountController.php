<?php

namespace App\Http\Controllers\Public;

use App\Contracts\SearchService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\SearchRequest;
use Illuminate\Http\JsonResponse;

class PropertyCountController extends Controller
{
    /**
     * How many listings a filter set would return, so the homepage search can say so before
     * the visitor commits. Uses the same filters as the results page, so the numbers agree.
     */
    public function __invoke(SearchRequest $request, SearchService $search): JsonResponse
    {
        $filters = $request->validated();
        $filters['location_area_ids'] = array_values(array_filter(array_map('intval', [
            ...(array) ($filters['location_area_ids'] ?? []),
            $filters['location_area_id'] ?? 0,
        ])));
        unset($filters['location_area_id']);

        return response()->json([
            'total' => $search->search($filters, 1, 1)->total(),
        ])->header('Cache-Control', 'public, max-age=60');
    }
}
