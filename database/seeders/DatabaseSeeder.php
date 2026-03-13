<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            QueueMvpSeeder::class,
        ]);

        if (! app()->runningUnitTests()) {
            $this->call([
                WilayahSeeder::class,
            ]);
        }

        $demoUsers = [
            ['name' => 'Administrator', 'email' => 'admin@example.com', 'role' => UserRole::Admin],
            ['name' => 'Frontdesk Demo', 'email' => 'frontdesk@example.com', 'role' => UserRole::Frontdesk],
            ['name' => 'Officer Demo', 'email' => 'officer@example.com', 'role' => UserRole::Officer],
            ['name' => 'Monitor Demo', 'email' => 'monitor@example.com', 'role' => UserRole::Monitor],
        ];

        foreach ($demoUsers as $demo) {
            User::query()->firstOrCreate(
                ['email' => $demo['email']],
                [
                    'name' => $demo['name'],
                    'role' => $demo['role']->value,
                    'email_verified_at' => now(),
                    'password' => 'password',
                ]
            );
        }
    }
}
