<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PropertyType;
use App\Support\TaggedCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PropertyTypeController extends Controller
{
    public function index(): View
    {
        $this->authorize('reference.manage');

        $types = PropertyType::query()->withCount('properties')->orderBy('label')->get();

        return view('admin.catalogue.types', [
            'types' => $types,
            'typesByCategory' => collect(PropertyType::categoryLabels())
                ->map(fn (string $label, string $category) => $types->where('category', $category)->values()),
            'profiles' => PropertyType::PROFILES,
            'categories' => PropertyType::categoryLabels(),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('reference.manage');
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_-]+$/', 'unique:property_types,key'],
            'label' => ['required', 'string', 'max:80'],
            'category' => ['required', Rule::in(PropertyType::CATEGORIES)],
            'field_profile' => ['required', Rule::in(PropertyType::PROFILES)],
        ]);
        $type = PropertyType::query()->create($validated + ['is_active' => true]);
        TaggedCache::flush(['search', 'homepage']);

        if ($request->wantsJson()) {
            return response()->json([
                'id' => $type->id,
                'label' => $type->label,
                'profile' => $type->field_profile,
                'category' => $type->category,
            ], 201);
        }

        return back()->with('status', $type->label.' added under '.$type->categoryLabel().'.');
    }

    public function update(Request $request, PropertyType $type): RedirectResponse
    {
        $this->authorize('reference.manage');
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:80'],
            'category' => ['required', Rule::in(PropertyType::CATEGORIES)],
            'field_profile' => ['required', Rule::in(PropertyType::PROFILES)],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $type->update([...$validated, 'is_active' => $request->boolean('is_active', $type->is_active)]);
        TaggedCache::flush(['search', 'homepage']);

        return back()->with('status', $type->label.' updated.');
    }

    public function deactivate(PropertyType $type): RedirectResponse
    {
        $this->authorize('reference.manage');
        $type->update(['is_active' => false]);

        return back()->with('status', 'Type deactivated.');
    }
}
