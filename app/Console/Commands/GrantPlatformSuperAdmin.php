<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class GrantPlatformSuperAdmin extends Command
{
    protected $signature = 'platform:grant {email : Email of the user to grant SuperAdmin role}';
    protected $description = 'Grant the SuperAdmin (platform provider) role to a user by email';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();
        if (!$user) {
            $this->error("User with email {$this->argument('email')} not found.");
            return self::FAILURE;
        }

        Role::firstOrCreate(['name' => 'SuperAdmin']);
        $user->assignRole('SuperAdmin');

        $this->info("Granted SuperAdmin to {$user->email}.");
        return self::SUCCESS;
    }
}
