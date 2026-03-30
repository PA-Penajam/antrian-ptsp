<?php

use App\Enums\UserRole;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('guests are redirected to the login page', function () {
    $response = get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    actingAs($user);

    $response = get(route('dashboard'));
    $response->assertOk();
});

test('dashboard shell is rendered based on user role', function () {
    $officer = User::factory()->create([
        'role' => UserRole::Officer->value,
    ]);
    $monitor = User::factory()->create([
        'role' => UserRole::Monitor->value,
    ]);
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
    ]);

    actingAs($officer);

    get(route('dashboard'))
        ->assertOk()
        ->assertSee('Modul Panggilan Petugas')
        ->assertDontSee('Ringkasan Monitoring')
        ->assertDontSee('Health Aplikasi');

    actingAs($monitor);

    get(route('dashboard'))
        ->assertOk()
        ->assertSee('Ringkasan Monitoring')
        ->assertDontSee('Modul Panggilan Petugas')
        ->assertDontSee('Health Aplikasi');

    actingAs($admin);

    get(route('dashboard'))
        ->assertOk()
        ->assertSee('Health Aplikasi')
        ->assertSee('Shortcut Manajemen')
        ->assertDontSee('Modul Panggilan Petugas');
});
