@extends('layouts.admin')
@section('title', $staffMember->name)

@section('content')
    <x-ui.page-header compact :title="$staffMember->name">
        <x-slot:eyebrow>Staff account</x-slot:eyebrow>
        <x-slot:actions>
            <a class="uh-btn-ghost uh-btn-sm" href="{{ route('admin.staff.index') }}">
                <x-icon name="chevron-left" class="size-4" />
                All staff
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="mt-6 grid max-w-4xl gap-6 lg:grid-cols-3">
        <form method="POST" action="{{ route('admin.staff.update', $staffMember) }}"
              class="uh-panel space-y-4 lg:col-span-2" x-data="uhForm" @submit="submit">
            @csrf
            @method('PUT')
            <x-ui.input name="name" label="Full name" :value="$staffMember->name" required />
            <x-ui.input name="email" label="Work email" type="email" dir="ltr" :value="$staffMember->email" required />
            <x-ui.input name="password" label="New password" type="password" autocomplete="new-password" optional
                        hint="Leave blank to keep the current password." />
            <x-ui.select name="role" label="Role">
                @foreach($roles as $role)
                    <option value="{{ $role->key }}" @selected($staffMember->hasRole($role->key))>{{ $role->label }}</option>
                @endforeach
            </x-ui.select>

            <div class="flex flex-wrap items-center gap-3 pt-1">
                <button type="submit" class="uh-btn-primary" :disabled="submitting">
                    <span class="uh-spinner" x-show="submitting" x-cloak></span>
                    <span x-text="submitting ? 'Saving…' : 'Save changes'">Save changes</span>
                </button>
                <a class="uh-btn-ghost" href="{{ route('admin.staff.index') }}">Cancel</a>
            </div>
        </form>

        <div class="space-y-6">
            <section class="uh-panel">
                <h2 class="uh-h4">Account</h2>
                <p class="mt-2">
                    @if($staffMember->is_active)
                        <x-ui.badge tone="success">Active</x-ui.badge>
                    @else
                        <x-ui.badge tone="outline">Deactivated</x-ui.badge>
                    @endif
                </p>

                @if($staffMember->is_active)
                    <p class="mt-3 text-xs leading-relaxed text-[var(--color-muted)]">
                        Deactivating blocks sign-in immediately. Their leads and notes stay in place.
                    </p>
                    <form method="POST" action="{{ route('admin.staff.deactivate', $staffMember) }}" class="mt-4"
                          x-data="uhConfirm('Deactivate {{ $staffMember->name }}? They will not be able to sign in.')">
                        @csrf
                        <button type="submit" class="uh-btn-danger uh-btn-sm uh-btn-block" @click="confirm($event)">
                            Deactivate account
                        </button>
                    </form>
                @endif
            </section>
        </div>
    </div>
@endsection
