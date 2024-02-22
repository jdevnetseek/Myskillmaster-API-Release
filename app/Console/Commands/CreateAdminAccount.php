<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateAdminAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create {email} {first_name?} {last_name?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates an admin account';

    const SUCCESS_RETURN_CODE = 0;
    const FAILED_RETURN_CODE = 1;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $email = $this->argument('email');

        if ($this->isEmailExists($email)) {
            $this->error("The email $email is already taken.");

            return self::FAILED_RETURN_CODE;
        }

        $this->createUser(
            $email,
            $this->argument('first_name'),
            $this->argument('last_name')
        );

        return self::FAILED_RETURN_CODE;
    }

    private function createUser($email, string $firstName = '', ?string $lastName = '')
    {
        $plainTextPassword = Str::random(16);

        DB::transaction(function () use ($email, $plainTextPassword, $firstName, $lastName) {
            $user = User::create([
                'email' => $email,
                'password' => Hash::make($plainTextPassword),
                'first_name' => $firstName ?? '',
                'last_name' => $lastName ?? '',
            ]);

            $user->assignRole(Role::ADMIN);
        });

        $this->info('User successfully created.');
        $this->info("Email: $email, Password: $plainTextPassword");
        $this->info('Please update your password.');
    }

    private function isEmailExists(string $email): bool
    {
        return User::whereEmail($email)->exists();
    }
}
