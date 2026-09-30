@extends('layouts.admin')
@section('title', 'Staff')

@section('content')
    <x-ui.page-header compact title="Staff"
                      description="Accounts that can sign in to this desk, and the role each one holds.">
        <x-slot:actions>
            <a class="uh-btn-primary uh-btn-sm" href="{{ route('admin.staff.create') }}">
                <x-icon name="plus" class="size-4" />
                Add staff
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="uh-panel-flush mt-6 overflow-hidden">
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
                        <tr>
                            <td class="font-medium">{{ $member->name }}</td>
                            <td class="text-xs" dir="ltr">{{ $member->email }}</td>
                            <td>{{ $member->roles->pluck('label')->join(', ') ?: '—' }}</td>
                            <td>
                                @if($member->is_active)
                                    <x-ui.badge tone="success">Active</x-ui.badge>
                                @else
                                    <x-ui.badge tone="outline">Deactivated</x-ui.badge>
                                @endif
                            </td>
                            <td class="whitespace-nowrap text-right">
                                <a class="uh-btn-ghost uh-btn-sm" href="{{ route('admin.staff.edit', $member) }}">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
