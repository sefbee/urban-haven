@extends('layouts.admin')
@section('title', 'Add staff')

@section('content')
    <x-ui.page-header compact title="Add a staff account">
        <x-slot:eyebrow>Administration</x-slot:eyebrow>
        <x-slot:actions>
            <a class="uh-btn-ghost uh-btn-sm" href="{{ route('admin.staff.index') }}">
                <x-icon name="chevron-left" class="size-4" />
                All staff
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <form method="POST" action="{{ route('admin.staff.store') }}" class="uh-admin-compose-main max-w-3xl"
          x-data="uhForm" @submit="submit">
        @csrf
        <div class="uh-admin-stack dd-form-steps">
            <section class="uh-panel space-y-4">
                <h2 class="uh-h4">Person</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input name="name" label="Full name" autocomplete="off" required autofocus />
                    <x-ui.input name="email" label="Work email" type="email" dir="ltr" autocomplete="off" required />
                    <x-ui.input name="phone" label="Direct phone" type="tel" dir="ltr" optional class="sm:col-span-2"
                                hint="Shown as the call number on listings this person is the contact for." />
                </div>
            </section>
            <section class="uh-panel space-y-4">
                <h2 class="uh-h4">Sign-in and access</h2>
                <x-ui.select name="role" label="Role" required hint="Roles decide which parts of the desk they can open.">
                    @foreach($roles as $role)
                        <option value="{{ $role->key }}" @selected(old('role') === $role->key)>{{ $role->label }}</option>
                    @endforeach
                </x-ui.select>
                @include('admin.staff._password', ['label' => 'Temporary password', 'required' => true, 'hint' => 'At least 12 characters. Ask them to change it after signing in.'])
            </section>
        </div>
        <div class="uh-admin-dock">
            <button type="submit" class="uh-btn-primary" :disabled="submitting">
                <span class="uh-spinner" x-show="submitting" x-cloak></span>
                <span x-text="submitting ? 'Creating…' : 'Create account'">Create account</span>
            </button>
            <a class="uh-btn-ghost" href="{{ route('admin.staff.index') }}">Cancel</a>
        </div>
    </form>
@endsection
