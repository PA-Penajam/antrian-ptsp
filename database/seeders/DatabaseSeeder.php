<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            WilayahSeeder::class,
        ]);

        // DEVELOPMENT ONLY: Uncomment untuk seeding pool, layanan, dan loket demo.
        // Untuk production deployment, data ini diinput manual via UI Admin.
        // $this->call([QueueMvpSeeder::class]);

        User::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'role' => UserRole::Admin->value,
                'email_verified_at' => now(),
                'password' => 'password',
            ]
        );
    }
}
