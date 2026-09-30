<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\AuditLogger;
use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\LocationArea;
use App\Models\PropertyType;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        abort_unless($this->userCanManage(), 403);

        return view('admin.settings.index', [
            'settings' => Setting::query()->orderBy('group')->orderBy('key')->get(),
            'areas' => LocationArea::query()->orderBy('name')->get(),
            'types' => PropertyType::query()->orderBy('label')->get(),
            'amenities' => Amenity::query()->orderBy('label')->get(),
        ]);
    }

    public function update(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless($this->userCanManage(), 403);
        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable', 'string'],
        ]);

        $existing = Setting::query()->whereIn('key', array_keys($validated['settings']))->get()->keyBy('key');

        foreach ($validated['settings'] as $key => $value) {
            Setting::set(
                $key,
                $value,
                $existing[$key]->group ?? 'general',
                $existing[$key]->cast ?? 'string',
            );
        }

        $auditLogger->record($request->user()->id, 'settings.updated', Setting::class, null, null, array_keys($validated['settings']), $request->ip());

        return back()->with('status', 'Settings saved.');
    }

    public function storeArea(Request $request): RedirectResponse
    {
        abort_unless($this->userCanManageReference(), 403);
        $validated = $request->validate(['name' => ['required', 'string'], 'city' => ['required', 'string']]);
        LocationArea::query()->create($validated + ['is_active' => true]);

        return back()->with('status', 'Area added.');
    }

    public function deactivateArea(LocationArea $area): RedirectResponse
    {
        abort_unless($this->userCanManageReference(), 403);
        $area->update(['is_active' => false]);

        return back()->with('status', 'Area deactivated.');
    }

    public function storeType(Request $request): RedirectResponse
    {
        abort_unless($this->userCanManageReference(), 403);
        $validated = $request->validate(['key' => ['required', 'string', 'unique:property_types,key'], 'label' => ['required', 'string']]);
        PropertyType::query()->create($validated + ['is_active' => true]);

        return back()->with('status', 'Type added.');
    }

    public function deactivateType(PropertyType $type): RedirectResponse
    {
        abort_unless($this->userCanManageReference(), 403);
        $type->update(['is_active' => false]);

        return back()->with('status', 'Type deactivated.');
    }

    public function storeAmenity(Request $request): RedirectResponse
    {
        abort_unless($this->userCanManageReference(), 403);
        $validated = $request->validate(['key' => ['required', 'string', 'unique:amenities,key'], 'label' => ['required', 'string']]);
        Amenity::query()->create($validated + ['is_active' => true]);

        return back()->with('status', 'Amenity added.');
    }

    public function deactivateAmenity(Amenity $amenity): RedirectResponse
    {
        abort_unless($this->userCanManageReference(), 403);
        $amenity->update(['is_active' => false]);

        return back()->with('status', 'Amenity deactivated.');
    }

    private function userCanManage(): bool
    {
        return (bool) request()->user()?->isOwnerAdmin();
    }

    private function userCanManageReference(): bool
    {
        $user = request()->user();

        return (bool) ($user?->isOwnerAdmin() || $user?->hasPermission('reference.manage'));
    }
}
