@extends('layouts.admin')
@section('title', $staffMember->name)

@section('content')
    <x-ui.page-header compact :title="$staffMember->name">
        <x-slot:eyebrow>Administration · User account</x-slot:eyebrow>
        <x-slot:actions>
            <a class="uh-btn-ghost uh-btn-sm" href="{{ route('admin.staff.index') }}">
                <x-icon name="chevron-left" class="size-4" />
                All staff
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="uh-admin-compose is-split">
        <form method="POST" action="{{ route('admin.staff.update', $staffMember) }}"
              class="uh-admin-compose-main" x-data="uhForm" @submit="submit">
            @csrf
            @method('PUT')
            <div class="uh-admin-stack dd-form-steps">
                <section class="uh-panel space-y-4">
                    <h2 class="uh-h4">Person</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.input name="name" label="Full name" :value="$staffMember->name" required />
                        <x-ui.input name="email" label="Work email" type="email" dir="ltr" :value="$staffMember->email" required />
                        <x-ui.input name="phone" label="Direct phone" type="tel" dir="ltr" :value="$staffMember->phone" optional class="sm:col-span-2"
                                    hint="Shown as the call number on listings this person is the contact for." />
                    </div>
                </section>
                <section class="uh-panel space-y-4">
                    <h2 class="uh-h4">Sign-in and access</h2>
                    <x-ui.select name="role" label="Role" hint="Roles decide which parts of the desk they can open.">
                        @foreach($roles as $role)
                            <option value="{{ $role->key }}" @selected(old('role') ? old('role') === $role->key : $staffMember->hasRole($role->key))>{{ $role->label }}</option>
                        @endforeach
                    </x-ui.select>
                    @include('admin.staff._password', ['label' => 'New password', 'required' => false, 'hint' => 'Leave blank to keep the current password.'])
                </section>
            </div>
            <div class="uh-admin-dock">
                <button type="submit" class="uh-btn-primary" :disabled="submitting">
                    <span class="uh-spinner" x-show="submitting" x-cloak></span>
                    <span x-text="submitting ? 'Saving…' : 'Save changes'">Save changes</span>
                </button>
                <a class="uh-btn-ghost" href="{{ route('admin.staff.index') }}">Cancel</a>
            </div>
        </form>

        <div class="uh-admin-compose-side">
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
                        Deactivating blocks sign-in immediately and ends their sessions.
                        @if($openLeadCount > 0)
                            They have <strong>{{ $openLeadCount }} open {{ Str::plural('lead', $openLeadCount) }}</strong>, which must move to someone else first.
                        @endif
                    </p>
                    @error('staff')<p class="mt-2 text-xs text-[var(--color-danger)]">{{ $message }}</p>@enderror
                    <form method="POST" action="{{ route('admin.staff.deactivate', $staffMember) }}" class="mt-4 space-y-3"
                          x-data="uhConfirm(@js('Deactivate '.$staffMember->name.'? They will not be able to sign in.'))">
                        @csrf
                        @if($openLeadCount > 0)
                            <x-ui.select name="reassign_to" label="Reassign open leads to" required>
                                <option value="">Choose a staff member</option>
                                @foreach($reassignOptions as $option)
                                    <option value="{{ $option->id }}">{{ $option->name }}</option>
                                @endforeach
                            </x-ui.select>
                        @endif
                        <button type="submit" class="uh-btn-danger uh-btn-sm uh-btn-block" @click="confirm($event)">
                            Deactivate account
                        </button>
                    </form>
                @endif
            </section>
        </div>
    </div>
@endsection
