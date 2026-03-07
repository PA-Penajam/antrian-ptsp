<?php

use App\Enums\QueueStatus;
use App\Enums\UserRole;
use App\Models\QueuePool;
use App\Models\QueueTicket;
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

test('admin can delete service without active tickets', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create(['name' => 'Layanan Hapus']);

    $response = $this->actingAs($admin)->delete("/admin/layanan/{$service->id}");

    $response->assertRedirect('/admin/layanan')
        ->assertSessionHas('status', 'Layanan berhasil dihapus.');

    $this->assertDatabaseMissing('services', ['id' => $service->id]);
});

test('admin cannot delete service with active tickets', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create(['name' => 'Layanan Aktif']);

    // Create active ticket (waiting status)
    QueueTicket::factory()->create([
        'service_id' => $service->id,
        'status' => QueueStatus::Waiting->value,
    ]);

    $response = $this->actingAs($admin)->delete("/admin/layanan/{$service->id}");

    $response->assertRedirect('/admin/layanan')
        ->assertSessionHas('error', 'Layanan tidak dapat dihapus karena memiliki antrian aktif.');

    $this->assertDatabaseHas('services', ['id' => $service->id]);
});

test('admin cannot delete service with booked tickets', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create();

    QueueTicket::factory()->create([
        'service_id' => $service->id,
        'status' => QueueStatus::Booked->value,
    ]);

    $response = $this->actingAs($admin)->delete("/admin/layanan/{$service->id}");

    $response->assertSessionHas('error');
    $this->assertDatabaseHas('services', ['id' => $service->id]);
});

test('admin cannot delete service with called tickets', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create();

    QueueTicket::factory()->create([
        'service_id' => $service->id,
        'status' => QueueStatus::Called->value,
    ]);

    $response = $this->actingAs($admin)->delete("/admin/layanan/{$service->id}");

    $response->assertSessionHas('error');
    $this->assertDatabaseHas('services', ['id' => $service->id]);
});

test('admin can delete service with completed or cancelled tickets', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create();

    // These statuses should not block deletion
    QueueTicket::factory()->create([
        'service_id' => $service->id,
        'status' => QueueStatus::Completed->value,
    ]);
    QueueTicket::factory()->create([
        'service_id' => $service->id,
        'status' => QueueStatus::Cancelled->value,
    ]);
    QueueTicket::factory()->create([
        'service_id' => $service->id,
        'status' => QueueStatus::Skipped->value,
    ]);

    $response = $this->actingAs($admin)->delete("/admin/layanan/{$service->id}");

    $response->assertSessionHas('status');
    $this->assertDatabaseMissing('services', ['id' => $service->id]);
});

test('search filters services by name', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
    $pool = QueuePool::factory()->create();
    $service1 = Service::factory()->for($pool)->create(['name' => 'Pendaftaran Online']);
    $service2 = Service::factory()->for($pool)->create(['name' => 'Pengaduan Langsung']);

    $response = $this->actingAs($admin)->get('/admin/layanan?search=Pendaftaran');

    $response->assertOk()
        ->assertSee($service1->name)
        ->assertDontSee($service2->name);
});

test('search filters services by code', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
    $pool = QueuePool::factory()->create();
    $service1 = Service::factory()->for($pool)->create(['code' => 'REG']);
    $service2 = Service::factory()->for($pool)->create(['code' => 'COM']);

    $response = $this->actingAs($admin)->get('/admin/layanan?search=REG');

    $response->assertOk()
        ->assertSee($service1->code)
        ->assertDontSee($service2->code);
});

test('pagination returns 10 items per page', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
    $pool = QueuePool::factory()->create();

    // Create 15 services
    Service::factory()->for($pool)->count(15)->create();

    $response = $this->actingAs($admin)->get('/admin/layanan');

    $response->assertOk();

    // First page should have 10 items
    $services = $response->viewData('services');
    expect($services)->toHaveCount(10);
    expect($services->total())->toBe(15);
});
