<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStaffRequest;
use App\Http\Requests\Admin\UpdateStaffRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\Auth\StaffService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        return view('admin.staff.index', [
            'staff' => User::query()->with('roles')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('admin.staff.create', [
            'roles' => Role::query()->orderBy('label')->get(),
        ]);
    }

    public function store(StoreStaffRequest $request, StaffService $staff): RedirectResponse
    {
        $staff->create($request->validated(), $request->user());

        return redirect()->route('admin.staff.index')->with('status', 'Staff account created.');
    }

    public function edit(User $staff): View
    {
        $this->authorize('update', $staff);

        return view('admin.staff.edit', [
            'staffMember' => $staff->load('roles'),
            'roles' => Role::query()->orderBy('label')->get(),
        ]);
    }

    public function update(UpdateStaffRequest $request, User $staff, StaffService $staffService): RedirectResponse
    {
        $staffService->update($staff, $request->validated(), $request->user());

        return redirect()->route('admin.staff.index')->with('status', 'Staff account updated.');
    }

    public function deactivate(User $staff, StaffService $staffService): RedirectResponse
    {
        $this->authorize('deactivate', $staff);
        $staffService->deactivate($staff, request()->user());

        return redirect()->route('admin.staff.index')->with('status', 'Staff account deactivated.');
    }
}
