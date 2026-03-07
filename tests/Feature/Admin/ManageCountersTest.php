<?php

use App\Enums\QueueStatus;
use App\Enums\UserRole;
use App\Models\Counter;
use App\Models\QueuePool;
use App\Models\User;

test('admin can list counters and update pool assignment with active status', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
    $oldPool = QueuePool::factory()->create(['code' => 'OLD']);
    $newPool = QueuePool::factory()->create(['code' => 'NEW']);
    $counter = Counter::factory()->for($oldPool)->create([
        'name' => 'Loket Umum 1',
        'code' => 'U1',
        'is_active' => true,
    ]);

    $listResponse = $this->actingAs($admin)->get('/admin/loket');
    $listResponse->assertOk()
        ->assertSee('Manajemen Loket')
        ->assertSee($counter->name)
        ->assertSee('min-h-screen')
        ->assertSee('name="queue_pool_id"', false)
        ->assertSee('name="is_active"', false);

    $updateResponse = $this->actingAs($admin)->put("/admin/loket/{$counter->id}", [
        'queue_pool_id' => $newPool->id,
        'is_active' => false,
        'name' => 'Loket Umum 1',
        'code' => 'U1',
        'sort_order' => 1,
    ]);

    $updateResponse->assertRedirect('/admin/loket');

    $this->assertDatabaseHas('counters', [
        'id' => $counter->id,
        'queue_pool_id' => $newPool->id,
        'is_active' => 0,
    ]);
});

test('non admin cannot access counter and service admin pages', function () {
    $nonAdmin = User::factory()->create([
        'role' => UserRole::Monitor->value,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($nonAdmin)->get('/admin/loket')->assertForbidden();
    $this->actingAs($nonAdmin)->get('/admin/layanan')->assertForbidden();
});

test('admin can create counter', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
    $pool = QueuePool::factory()->create(['code' => 'POOL1']);

    $response = $this->actingAs($admin)->post('/admin/loket', [
        'name' => 'Loket Baru',
        'code' => 'LB01',
        'queue_pool_id' => $pool->id,
        'sort_order' => 5,
        'is_active' => true,
    ]);

    $response->assertRedirect('/admin/loket')
        ->assertSessionHas('status', 'Loket berhasil dibuat.');

    $this->assertDatabaseHas('counters', [
        'name' => 'Loket Baru',
        'code' => 'LB01',
        'queue_pool_id' => $pool->id,
        'sort_order' => 5,
        'is_active' => 1,
    ]);
});

test('admin cannot delete counter with active tickets', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
    $pool = QueuePool::factory()->create();
    $counter = Counter::factory()->for($pool)->create();
    $service = \App\Models\Service::factory()->create();

    // Create active ticket (waiting status)
    \App\Models\QueueTicket::factory()->create([
        'counter_id' => $counter->id,
        'service_id' => $service->id,
        'queue_pool_id' => $pool->id,
        'status' => QueueStatus::Waiting,
    ]);

    $response = $this->actingAs($admin)->delete("/admin/loket/{$counter->id}");

    $response->assertRedirect('/admin/loket')
        ->assertSessionHas('error', 'Loket tidak dapat dihapus karena memiliki antrian aktif.');

    $this->assertDatabaseHas('counters', ['id' => $counter->id]);
});

test('admin can delete counter without active tickets', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
    $pool = QueuePool::factory()->create();
    $counter = Counter::factory()->for($pool)->create();

    $response = $this->actingAs($admin)->delete("/admin/loket/{$counter->id}");

    $response->assertRedirect('/admin/loket')
        ->assertSessionHas('status', 'Loket berhasil dihapus.');

    $this->assertDatabaseMissing('counters', ['id' => $counter->id]);
});

test('empty state shows when no counters', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get('/admin/loket');

    $response->assertOk()
        ->assertSee('Belum ada loket');
});
