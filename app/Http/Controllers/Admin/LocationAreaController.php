<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LocationArea;
use App\Support\TaggedCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationAreaController extends Controller
{
    public function index(): View
    {
        $this->authorize('reference.manage');

        return view('admin.catalogue.areas', [
            'areas' => LocationArea::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('reference.manage');
        $validated = $request->validate(['name' => ['required', 'string', 'max:120'], 'city' => ['required', 'string', 'max:120']]);
        $area = LocationArea::query()->create($validated + ['is_active' => true]);
        TaggedCache::flush(['search', 'homepage']);

        if ($request->wantsJson()) {
            return response()->json([
                'id' => $area->id,
                'label' => $area->name.' — '.$area->city,
            ], 201);
        }

        return back()->with('status', 'Area added.');
    }

    public function update(Request $request, LocationArea $area): RedirectResponse
    {
        $this->authorize('reference.manage');
        $bounds = config('urbanhaven.maps.bounds');
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'city' => ['required', 'string', 'max:120'],
            'intro' => ['nullable', 'string', 'max:2000'],
            'meta_description' => ['nullable', 'string', 'max:160'],
            'lat' => ['nullable', 'required_with:lng', 'numeric', 'between:'.$bounds['south'].','.$bounds['north']],
            'lng' => ['nullable', 'required_with:lat', 'numeric', 'between:'.$bounds['west'].','.$bounds['east']],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $area->update([...$validated, 'is_active' => $request->boolean('is_active', $area->is_active)]);
        TaggedCache::flush(['search', 'homepage']);

        return back()->with('status', $area->name.' updated.');
    }

    public function deactivate(LocationArea $area): RedirectResponse
    {
        $this->authorize('reference.manage');
        $area->update(['is_active' => false]);

        return back()->with('status', 'Area deactivated.');
    }
}
