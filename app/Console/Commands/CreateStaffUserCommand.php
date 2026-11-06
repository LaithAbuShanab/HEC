<?php

namespace App\Console\Commands;

use App\Domain\Access\Role;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

#[Signature('hec:create-user {--role=}')]
#[Description('Create a staff user for the admin panel')]
class CreateStaffUserCommand extends Command
{
    public function handle(): int
    {
        $role = Role::tryFrom((string) $this->option('role'))
            ?? Role::from(select('Role', Role::options('en'), default: Role::Admin->value));

        $data = [
            'name' => text('Name', required: true),
            'email' => text('Email', required: true),
            'password' => password('Password', required: true),
        ];

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', Password::min(12)->letters()->numbers()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create($data);
        $user->assignRole($role->value);

        $this->info("Created {$role->label('en')} {$user->email}");

        return self::SUCCESS;
    }
}
