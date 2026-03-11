<?php

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Hash;

test('database seeder creates the default admin user', function () {
    $this->seed(DatabaseSeeder::class);

    $admin = User::query()->where('email', 'admin@example.com')->first();

    expect($admin)->not->toBeNull()
        ->and($admin->name)->toBe('Administrator')
        ->and($admin->role)->toBe(UserRole::Admin)
        ->and($admin->email_verified_at)->not->toBeNull()
        ->and(Hash::check('password', $admin->password))->toBeTrue();
});

test('database seeder does not duplicate the default admin user', function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    expect(
        User::query()
            ->where('email', 'admin@example.com')
            ->count()
    )->toBe(1);
});
