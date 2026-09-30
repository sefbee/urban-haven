@extends('layouts.admin')
@section('title', 'Add staff')

@section('content')
    <x-ui.page-header compact title="Add a staff account"
                      description="The new member signs in with this email and the temporary password you set here.">
        <x-slot:actions>
            <a class="uh-btn-ghost uh-btn-sm" href="{{ route('admin.staff.index') }}">
                <x-icon name="chevron-left" class="size-4" />
                All staff
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <form method="POST" action="{{ route('admin.staff.store') }}" class="uh-panel mt-6 max-w-xl space-y-4"
          x-data="uhForm" @submit="submit">
        @csrf
        <x-ui.input name="name" label="Full name" autocomplete="off" required />
        <x-ui.input name="email" label="Work email" type="email" dir="ltr" autocomplete="off" required />
        <x-ui.input name="password" label="Temporary password" type="password" autocomplete="new-password" required
                    hint="At least 12 characters. Ask them to change it after signing in." />
        <x-ui.select name="role" label="Role" required hint="Roles decide which parts of the desk they can open.">
            @foreach($roles as $role)
                <option value="{{ $role->key }}" @selected(old('role') === $role->key)>{{ $role->label }}</option>
            @endforeach
        </x-ui.select>

        <div class="flex flex-wrap items-center gap-3 pt-1">
            <button type="submit" class="uh-btn-primary" :disabled="submitting">
                <span class="uh-spinner" x-show="submitting" x-cloak></span>
                <span x-text="submitting ? 'Creating…' : 'Create account'">Create account</span>
            </button>
            <a class="uh-btn-ghost" href="{{ route('admin.staff.index') }}">Cancel</a>
        </div>
    </form>
@endsection
