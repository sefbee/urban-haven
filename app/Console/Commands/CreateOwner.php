<?php

namespace App\Console\Commands;

use App\Contracts\AuditLogger;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolesPermissionsSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

#[Signature('uh:create-owner {email} {--name=}')]
#[Description('Create the first owner administrator account (prompts for the password)')]
class CreateOwner extends Command
{
    public function handle(AuditLogger $audit): int
    {
        $email = (string) $this->argument('email');
        $name = (string) ($this->option('name') ?: $this->ask('Full name'));
        $password = (string) $this->secret('Password (min 12 characters)');

        $validator = Validator::make(compact('email', 'name', 'password'), [
            'email' => ['required', 'email', 'unique:users,email'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', Password::min(12)->mixedCase()->numbers()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        (new RolesPermissionsSeeder)->run();

        $user = User::query()->create(['name' => $name, 'email' => $email, 'password' => $password, 'is_active' => true]);
        $user->roles()->sync([Role::query()->where('key', Role::OWNER_ADMIN)->value('id')]);
        $audit->record(null, 'staff.owner_created_cli', User::class, $user->id, null, ['email' => $email]);

        $this->info("Owner administrator {$email} created. Two-factor setup is required at first sign-in.");

        return self::SUCCESS;
    }
}
