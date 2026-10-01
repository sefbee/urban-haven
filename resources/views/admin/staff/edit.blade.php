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
            <x-ui.input name="phone" label="Direct phone" type="tel" dir="ltr" :value="$staffMember->phone" optional
                        hint="Shown as the call number on listings this person is the contact for." />
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

            <section class="uh-panel">
                <h2 class="uh-h4">Two-factor authentication</h2>
                <p class="mt-2">
                    @if($staffMember->hasMfaEnabled())
                        <x-ui.badge tone="success">Enabled {{ \App\Support\DisplayTimezone::format($staffMember->mfa_enabled_at, 'j M Y') }}</x-ui.badge>
                    @else
                        <x-ui.badge tone="outline">Not set up</x-ui.badge>
                    @endif
                </p>
                @if($staffMember->hasMfaEnabled())
                    <p class="mt-3 text-xs leading-relaxed text-[var(--color-muted)]">Reset if they lost their phone and recovery codes. They will enrol again at next sign-in.</p>
                    <form method="POST" action="{{ route('admin.staff.mfa.reset', $staffMember) }}" class="mt-4"
                          x-data="uhConfirm(@js('Reset two-factor authentication for '.$staffMember->name.'?'))">
                        @csrf
                        <button type="submit" class="uh-btn-ghost uh-btn-sm uh-btn-block" @click="confirm($event)">Reset two-factor</button>
                    </form>
                @endif
            </section>
        </div>
    </div>
@endsection
