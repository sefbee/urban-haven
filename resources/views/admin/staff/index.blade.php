@extends('layouts.admin')
@section('title', 'Users & roles')

@section('content')
    <x-ui.page-header compact title="Users & roles"
                      description="Accounts that can sign in to this desk, and the role each one holds.">
        <x-slot:eyebrow>Administration</x-slot:eyebrow>
        <x-slot:actions>
            <a class="uh-btn-primary uh-btn-sm" href="{{ route('admin.staff.create') }}">
                <x-icon name="plus" class="size-4" />
                Add staff
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="uh-panel-flush overflow-hidden">
        <div class="uh-table-scroll">
            <table class="uh-table">
                <caption class="sr-only">Staff accounts</caption>
                <thead>
                    <tr>
                        <th scope="col">Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Role</th>
                        <th scope="col">Account</th>
                        <th scope="col"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($staff as $member)
                        <tr class="uh-admin-clickrow">
                            <td class="font-medium">
                                <a class="uh-admin-row-main uh-link-quiet font-medium" href="{{ route('admin.staff.edit', $member) }}">{{ $member->name }}</a>
                            </td>
                            <td class="text-xs" dir="ltr">{{ $member->email }}</td>
                            <td>{{ $member->roles->pluck('label')->join(', ') ?: '—' }}</td>
                            <td>
                                @if($member->is_active)
                                    <x-ui.badge tone="success">Active</x-ui.badge>
                                @else
                                    <x-ui.badge tone="outline">Deactivated</x-ui.badge>
                                @endif
                            </td>
                            <td class="uh-admin-row-actions">
                                <a class="uh-btn-ghost uh-btn-sm" href="{{ route('admin.staff.edit', $member) }}">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
