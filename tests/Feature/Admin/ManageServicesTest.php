<?php

use App\Enums\UserRole;
use App\Models\QueuePool;
use App\Models\Service;
use App\Models\User;

test('admin can list services', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $service = Service::factory()->for($pool)->create(['name' => 'Pendaftaran']);

    $response = $this->actingAs($admin)->get('/admin/layanan');

    $response->assertOk()
        ->assertSee('Manajemen Layanan')
        ->assertSee($service->name)
        ->assertSee('min-h-screen')
        ->assertSee('Tambah Layanan')
        ->assertSee('name="name"', false)
        ->assertSee('name="code"', false)
        ->assertSee('name="queue_pool_id"', false);
});

test('admin can create and update service', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
    $activePool = QueuePool::factory()->create(['code' => 'AKTIF', 'is_active' => true]);

    $createResponse = $this->actingAs($admin)->post('/admin/layanan', [
        'queue_pool_id' => $activePool->id,
        'name' => 'Layanan Test',
        'code' => 'TST',
        'slug' => 'layanan-test',
        'description' => 'Deskripsi',
        'requirements' => 'Syarat',
        'is_active' => true,
        'booking_enabled' => true,
        'walk_in_enabled' => true,
        'daily_quota' => 100,
        'sort_order' => 10,
    ]);

    $createResponse->assertRedirect('/admin/layanan');

    $service = Service::query()->where('code', 'TST')->firstOrFail();

    $updateResponse = $this->actingAs($admin)->put("/admin/layanan/{$service->id}", [
        'name' => 'Layanan Test Updated',
        'description' => 'Deskripsi baru',
        'is_active' => false,
    ]);

    $updateResponse->assertRedirect('/admin/layanan');

    $this->assertDatabaseHas('services', [
        'id' => $service->id,
        'name' => 'Layanan Test Updated',
        'is_active' => 0,
    ]);
});

test('admin cannot assign service to inactive pool', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
    $inactivePool = QueuePool::factory()->create(['is_active' => false]);

    $response = $this->actingAs($admin)->post('/admin/layanan', [
        'queue_pool_id' => $inactivePool->id,
        'name' => 'Layanan Invalid',
        'code' => 'INV',
        'slug' => 'layanan-invalid',
        'is_active' => true,
        'booking_enabled' => true,
        'walk_in_enabled' => true,
        'sort_order' => 1,
    ]);

    $response->assertSessionHasErrors(['queue_pool_id']);
    $this->assertDatabaseMissing('services', ['code' => 'INV']);
});
