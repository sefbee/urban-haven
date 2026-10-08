<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LocationArea;
use App\Services\Seo\RedirectResolver;
use App\Support\SeoFields;
use App\Support\TaggedCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Stevebauman\Purify\Facades\Purify;

class LocationAreaController extends Controller
{
    public function index(): View
    {
        $this->authorize('reference.manage');

        $areas = LocationArea::query()->with('seoOverride')->withCount('properties')->orderBy('country')->orderBy('city')->orderBy('name')->get();

        return view('admin.catalogue.areas', [
            'areas' => $areas,
            'countries' => $areas->pluck('country')->push(LocationArea::DEFAULT_COUNTRY)->filter()->unique()->sort()->values(),
            'cities' => $areas->pluck('city')->filter()->unique()->sort()->values(),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('reference.manage');
        $validated = $request->validate([
            'country' => ['nullable', 'string', 'max:80'],
            'city' => ['required', 'string', 'max:120'],
            'name' => ['required', 'string', 'max:120'],
        ]);
        $area = LocationArea::query()->create([
            ...$validated,
            'country' => trim((string) ($validated['country'] ?? '')) ?: LocationArea::DEFAULT_COUNTRY,
            'city' => trim($validated['city']),
            'name' => trim($validated['name']),
            'is_active' => true,
        ]);
        TaggedCache::flush(['search', 'homepage']);

        if ($request->wantsJson()) {
            return response()->json([
                'id' => $area->id,
                'label' => $area->label(),
                'name' => $area->name,
                'city' => $area->city,
                'country' => $area->country,
            ], 201);
        }

        return back()->with('status', $area->name.' added under '.$area->city.'.');
    }

    public function update(Request $request, LocationArea $area, RedirectResolver $redirects): RedirectResponse
    {
        $this->authorize('reference.manage');
        $bounds = config('urbanhaven.maps.bounds');

        if ($request->has('slug')) {
            $request->merge(['slug' => SeoFields::normaliseSlug($request->input('slug'))]);
        }

        $validated = $request->validate([
            'country' => ['nullable', 'string', 'max:80'],
            'city' => ['required', 'string', 'max:120'],
            'name' => ['required', 'string', 'max:120'],
            'slug' => SeoFields::slugRules('location_areas', $area),
            'intro' => ['nullable', 'string', 'max:10000'],
            'lat' => ['nullable', 'required_with:lng', 'numeric', 'between:'.$bounds['south'].','.$bounds['north']],
            'lng' => ['nullable', 'required_with:lat', 'numeric', 'between:'.$bounds['west'].','.$bounds['east']],
            'is_active' => ['sometimes', 'boolean'],
            ...SeoFields::rules(),
        ]);

        $validated['intro'] = Purify::clean((string) ($validated['intro'] ?? ''));

        DB::transaction(function () use ($area, $validated, $request, $redirects): void {
            $oldSlug = $area->slug;
            $hadPage = $area->hasLandingPage();
            $attributes = collect($validated)->except([...SeoFields::inputNames(), 'slug'])->all();

            $area->fill([
                ...$attributes,
                'country' => trim((string) ($validated['country'] ?? '')) ?: LocationArea::DEFAULT_COUNTRY,
                'is_active' => $request->boolean('is_active', $area->is_active),
            ]);

            if (filled($validated['slug'] ?? null)) {
                $area->slug = $validated['slug'];
            }

            $area->save();
            SeoFields::sync($area, $validated);

            if ($hadPage && $oldSlug && $oldSlug !== $area->slug) {
                $redirects->store('/locations/'.$oldSlug, '/locations/'.$area->slug, 301, 'Area URL changed', $request->user());
            }
        });

        TaggedCache::flush(['search', 'homepage', 'sitemap']);

        return back()->with('status', $area->name.' updated.');
    }

    public function deactivate(LocationArea $area): RedirectResponse
    {
        $this->authorize('reference.manage');
        $area->update(['is_active' => false]);
        TaggedCache::flush(['search', 'homepage', 'sitemap']);

        return back()->with('status', 'Area deactivated.');
    }
}
