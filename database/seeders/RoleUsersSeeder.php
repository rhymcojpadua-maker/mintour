<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleUsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'           => 'Super Admin',
                'email'          => env('Mintour_ADMIN_EMAIL', 'admin@mintour.local'),
                'password'       => Hash::make(env('Mintour_ADMIN_PASSWORD', 'admin123')),
                'role'           => Role::SuperAdmin,
                'preferred_lang' => 'en',
            ],
            [
                'name'           => 'Tourism Staff',
                'email'          => 'staff@mintour.local',
                'password'       => Hash::make('staff123'),
                'role'           => Role::Staff,
                'preferred_lang' => 'en',
            ],
            [
                'name'           => 'Maria Santos',
                'email'          => 'maria@example.com',
                'password'       => Hash::make('user123'),
                'role'           => Role::User,
                'preferred_lang' => 'fil',
            ],
            [
                'name'           => 'Juan Dela Cruz',
                'email'          => 'juan@example.com',
                'password'       => Hash::make('user123'),
                'role'           => Role::User,
                'preferred_lang' => 'en',
            ],
            [
                'name'           => 'Anna Reyes',
                'email'          => 'anna@example.com',
                'password'       => Hash::make('user123'),
                'role'           => Role::User,
                'preferred_lang' => 'fil',
            ],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(['email' => $u['email']], $u);
        }

        $this->command->info('✔ Seeded ' . count($users) . ' users.');
        $this->command->line('  Admin:  ' . env('Mintour_ADMIN_EMAIL', 'admin@mintour.local')
            . ' / ' . env('Mintour_ADMIN_PASSWORD', 'admin123'));
        $this->command->line('  Staff:  staff@mintour.local / staff123');
        $this->command->line('  User:   maria@example.com / user123');
    }
}
