<?php
use Illuminate\Support\Facades\Route;
Route::get('/test-map', function (\Illuminate\Http\Request $request, \App\Services\SearchService $search) {
    $request->merge(['facing' => 'north']);
    $data = app(\App\Http\Controllers\Public\PropertySearchController::class)->browseData($request, $search);
    return view('public.properties.map', $data);
});
