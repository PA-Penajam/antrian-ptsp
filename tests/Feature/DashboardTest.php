<?php

use App\Enums\UserRole;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
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

    $this->actingAs($officer)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Modul Panggilan Petugas')
        ->assertDontSee('Ringkasan Monitoring')
        ->assertDontSee('Health Aplikasi');

    $this->actingAs($monitor)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Ringkasan Monitoring')
        ->assertDontSee('Modul Panggilan Petugas')
        ->assertDontSee('Health Aplikasi');

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Health Aplikasi')
        ->assertSee('Shortcut Manajemen')
        ->assertDontSee('Modul Panggilan Petugas');
});
