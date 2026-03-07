<?php

use App\Enums\UserRole;
use App\Models\User;

test('public landing page exposes PTSP public links', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('/antrian')
        ->assertSee('/antrian/cek')
        ->assertSee('/display');
});

test('dashboard shows role-aware navigation links', function () {
    $frontdesk = User::factory()->create([
        'role' => UserRole::Frontdesk->value,
        'email_verified_at' => now(),
    ]);
    $monitor = User::factory()->create([
        'role' => UserRole::Monitor->value,
        'email_verified_at' => now(),
    ]);
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($frontdesk)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('/frontdesk/antrian')
        ->assertDontSee('/admin/layanan');

    $this->actingAs($monitor)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('/laporan/antrian')
        ->assertDontSee('/admin/layanan');

    $this->actingAs($admin)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('/admin/layanan');
});
