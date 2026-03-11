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
