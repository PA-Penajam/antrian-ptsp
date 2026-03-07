<?php

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
        ->assertSee('Perbarui Loket')
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
