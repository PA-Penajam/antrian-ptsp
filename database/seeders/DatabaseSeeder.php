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

        User::query()->firstOrCreate(
            ['email' => 'officer@example.com'],
            [
                'name' => 'Petugas Loket Demo',
                'role' => UserRole::Officer->value,
                'email_verified_at' => now(),
                'password' => 'password',
            ]
        );

        User::query()->firstOrCreate(
            ['email' => 'frontdesk@example.com'],
            [
                'name' => 'Petugas Frontdesk Demo',
                'role' => UserRole::Frontdesk->value,
                'email_verified_at' => now(),
                'password' => 'password',
            ]
        );

        User::query()->firstOrCreate(
            ['email' => 'monitor@example.com'],
            [
                'name' => 'Pimpinan Monitor Demo',
                'role' => UserRole::Monitor->value,
                'email_verified_at' => now(),
                'password' => 'password',
            ]
        );
    }
}
