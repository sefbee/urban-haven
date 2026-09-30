<?php

namespace App\Services\Auth;

use App\Contracts\AuditLogger;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StaffService
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function create(array $data, User $actor): User
    {
        return DB::transaction(function () use ($data, $actor) {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
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

    public function update(User $user, array $data, User $actor): User
    {
        return DB::transaction(function () use ($user, $data, $actor) {
            $old = ['name' => $user->name, 'email' => $user->email];

            $user->fill([
                'name' => $data['name'],
                'email' => $data['email'],
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

    public function deactivate(User $user, User $actor): void
    {
        $user->forceFill(['is_active' => false])->save();
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
