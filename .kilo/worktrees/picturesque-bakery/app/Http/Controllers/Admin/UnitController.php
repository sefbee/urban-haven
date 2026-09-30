<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\InventoryService;
use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function create(Property $property): View
    {
        $this->authorize('update', $property);

        return view('admin.units.create', ['property' => $property]);
    }

    public function store(Request $request, Property $property, InventoryService $inventory): RedirectResponse
    {
        $this->authorize('update', $property);
        $validated = $request->validate([
            'unit_number' => ['required', 'string', 'max:50'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:available,reserved,sold,rented'],
            'notes' => ['nullable', 'string'],
        ]);
        $validated['property_id'] = $property->id;
        $inventory->createUnit($validated, $request->user());

        return redirect()->route('admin.properties.edit', $property)->with('status', 'Unit added.');
    }

    public function destroy(Unit $unit, InventoryService $inventory): RedirectResponse
    {
        $this->authorize('update', $unit->property);
        $property = $unit->property;
        $inventory->deleteUnit($unit, request()->user());

        return redirect()->route('admin.properties.edit', $property)->with('status', 'Unit removed.');
    }
}
