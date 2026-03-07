<?php

use App\Enums\UserRole;
use App\Models\QueuePool;
use App\Models\Service;
use App\Models\User;

test('admin can list users with role management page', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
    $officer = User::factory()->create([
        'name' => 'Petugas Uji',
        'role' => UserRole::Officer->value,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get('/admin/users');

    $response->assertOk()
        ->assertSee('Manajemen User')
        ->assertSee($officer->name)
        ->assertSee('name="role"', false)
        ->assertSee('name="services[]"', false);
});

test('admin can update user role and allowed services', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $serviceA = Service::factory()->for($pool)->create(['name' => 'Pendaftaran']);
    $serviceB = Service::factory()->for($pool)->create(['name' => 'Informasi']);
    $officer = User::factory()->create([
        'role' => UserRole::Monitor->value,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($admin)->put("/admin/users/{$officer->id}", [
        'name' => $officer->name,
        'email' => $officer->email,
        'role' => UserRole::Officer->value,
        'services' => [$serviceA->id, $serviceB->id],
    ]);

    $response->assertRedirect('/admin/users');

    $officer->refresh();

    expect($officer->role)->toBe(UserRole::Officer)
        ->and($officer->services()->pluck('services.id')->sort()->values()->all())
        ->toBe([$serviceA->id, $serviceB->id]);
});

test('non admin cannot access user management pages', function () {
    $monitor = User::factory()->create([
        'role' => UserRole::Monitor->value,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($monitor)->get('/admin/users')->assertForbidden();
    $this->actingAs($monitor)->get('/admin/roles')->assertForbidden();
    $this->actingAs($monitor)->get('/admin/izin-layanan')->assertForbidden();
});
