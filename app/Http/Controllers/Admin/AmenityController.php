<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AmenityController extends Controller
{
    public function index(): View
    {
        $this->authorize('reference.manage');

        return view('admin.catalogue.amenities', [
            'amenities' => Amenity::query()->orderBy('label')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('reference.manage');
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_-]+$/', 'unique:amenities,key'],
            'label' => ['required', 'string', 'max:80'],
        ]);
        $amenity = Amenity::query()->create($validated + ['is_active' => true]);

        if ($request->wantsJson()) {
            return response()->json([
                'id' => $amenity->id,
                'label' => $amenity->label,
            ], 201);
        }

        return back()->with('status', 'Amenity added.');
    }

    public function deactivate(Amenity $amenity): RedirectResponse
    {
        $this->authorize('reference.manage');
        $amenity->update(['is_active' => false]);

        return back()->with('status', 'Amenity deactivated.');
    }
}
