<?php

use App\Enums\UserRole;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('public landing page exposes PTSP public links', function () {
    $response = get('/');

    $response->assertOk()
        ->assertSee('/antrian')
        ->assertSee('/antrian/cek');
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

    actingAs($frontdesk);

    get('/dashboard')
        ->assertOk()
        ->assertSee('/frontdesk/antrian')
        ->assertSee('Modul Panggilan Petugas')
        ->assertDontSee('/admin/layanan')
        ->assertDontSee('/laporan/bulanan');

    actingAs($monitor);

    get('/dashboard')
        ->assertOk()
        ->assertSee('/laporan/antrian')
        ->assertSee('Ringkasan Monitoring')
        ->assertDontSee('/admin/layanan')
        ->assertSee('/laporan/bulanan');

    actingAs($admin);

    get('/dashboard')
        ->assertOk()
        ->assertSee('/admin/layanan')
        ->assertSee('Health Aplikasi')
        ->assertSee('Shortcut Manajemen')
        ->assertSee('/laporan/bulanan');
});
