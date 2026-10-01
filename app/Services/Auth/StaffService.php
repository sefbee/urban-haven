<?php

namespace App\Services\Auth;

use App\Contracts\AuditLogger;
use App\Contracts\LeadService;
use App\Models\Lead;
use App\Models\LeadFollowUp;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StaffService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly LeadService $leads,
    ) {}

    /**
     * @param  array{name: string, email: string, password: string, role: string, phone?: ?string}  $data
     */
    public function create(array $data, User $actor): User
    {
        return DB::transaction(function () use ($data, $actor) {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'is_active' => true,
            ]);

            $role = Role::query()->where('key', $data['role'])->firstOrFail();
            $user->roles()->sync([$role->id]);

            $this->auditLogger->record(
                $actor->id,
                'staff.created',
                User::class,
                $user->id,
                null,
                ['email' => $user->email, 'role' => $role->key],
                request()->ip(),
            );

            return $user;
        });
    }

    /**
     * @param  array{name: string, email: string, password?: ?string, role?: ?string, phone?: ?string}  $data
     */
    public function update(User $user, array $data, User $actor): User
    {
        return DB::transaction(function () use ($user, $data, $actor) {
            $old = ['name' => $user->name, 'email' => $user->email];

            $user->fill([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? $user->phone,
            ]);

            if (! empty($data['password'])) {
                $user->password = $data['password'];
                $this->invalidateSessions($user);
            }

            $user->save();

            if (! empty($data['role'])) {
                $role = Role::query()->where('key', $data['role'])->firstOrFail();
                $user->roles()->sync([$role->id]);
            }

            $this->auditLogger->record(
                $actor->id,
                'staff.updated',
                User::class,
                $user->id,
                $old,
                ['name' => $user->name, 'email' => $user->email, 'role' => $data['role'] ?? null],
                request()->ip(),
            );

            return $user;
        });
    }

    /**
     * Open leads must move to another active staff member first so no lead is orphaned.
     */
    public function deactivate(User $user, User $actor, ?User $reassignTo = null): void
    {
        if ($user->is($actor)) {
            throw ValidationException::withMessages(['staff' => 'You cannot deactivate your own account.']);
        }

        if ($user->isOwnerAdmin() && User::query()->where('is_active', true)->whereKeyNot($user->id)->whereHas('roles', fn ($q) => $q->where('key', Role::OWNER_ADMIN))->doesntExist()) {
            throw ValidationException::withMessages(['staff' => 'At least one active owner administrator is required.']);
        }

        $openLeads = $user->assignedLeads()->whereNotIn('status', Lead::CLOSED_STATUSES);

        if ($openLeads->exists() && ($reassignTo === null || ! $reassignTo->is_active || $reassignTo->is($user))) {
            throw ValidationException::withMessages(['reassign_to' => 'Choose an active staff member to take over this person\'s open leads.']);
        }

        DB::transaction(function () use ($user, $actor, $reassignTo, $openLeads): void {
            if ($reassignTo !== null) {
                $openLeads->get()->each(fn (Lead $lead) => $this->leads->assign($lead, $reassignTo, $actor));
                LeadFollowUp::query()->where('user_id', $user->id)->where('status', LeadFollowUp::OPEN)->update(['user_id' => $reassignTo->id]);
            }

            $user->forceFill(['is_active' => false])->save();
        });

        $this->invalidateSessions($user);

        $this->auditLogger->record(
            $actor->id,
            'staff.deactivated',
            User::class,
            $user->id,
            ['is_active' => true],
            ['is_active' => false],
            request()->ip(),
        );
    }

    public function markLogin(User $user): void
    {
        $user->forceFill(['last_login_at' => now()])->save();
        $this->auditLogger->record($user->id, 'auth.login', User::class, $user->id, null, null, request()->ip());
    }

    public function recordFailedLogin(string $email, ?string $ip): void
    {
        $this->auditLogger->record(null, 'auth.login_failed', User::class, null, null, ['email' => $email], $ip);
    }

    public function invalidateSessions(User $user): void
    {
        DB::table('sessions')->where('user_id', $user->id)->delete();
    }
}
