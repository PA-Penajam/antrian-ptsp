<?php

use App\Enums\QueueStatus;
use App\Enums\UserRole;
use App\Models\Counter;
use App\Models\CounterSession;
use App\Models\QueuePool;
use App\Models\QueueTicket;
use App\Models\Service;
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
    $service = Service::factory()->create();

    // Create active ticket (waiting status)
    QueueTicket::factory()->create([
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

test('admin can create pool', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($admin)->post('/admin/loket/pool', [
        'name' => 'Pool Test',
        'code' => 'TEST',
        'letter_code' => 'T',
        'is_active' => true,
    ]);

    $response->assertRedirect('/admin/loket')
        ->assertSessionHas('status', 'Pool antrian berhasil dibuat.');

    $this->assertDatabaseHas('queue_pools', [
        'name' => 'Pool Test',
        'code' => 'TEST',
        'letter_code' => 'T',
        'is_active' => 1,
    ]);
});

test('admin can update pool', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
    $pool = QueuePool::factory()->create([
        'name' => 'Pool Lama',
        'code' => 'OLD',
        'letter_code' => 'O',
    ]);

    $response = $this->actingAs($admin)->put("/admin/loket/pool/{$pool->id}", [
        'name' => 'Pool Baru',
        'code' => 'NEW',
        'letter_code' => 'N',
        'is_active' => false,
    ]);

    $response->assertRedirect('/admin/loket')
        ->assertSessionHas('status', 'Pool antrian berhasil diperbarui.');

    $this->assertDatabaseHas('queue_pools', [
        'id' => $pool->id,
        'name' => 'Pool Baru',
        'code' => 'NEW',
        'letter_code' => 'N',
        'is_active' => 0,
    ]);
});

test('admin cannot delete pool with services', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
    $pool = QueuePool::factory()->create();
    Service::factory()->for($pool)->create();

    $response = $this->actingAs($admin)->delete("/admin/loket/pool/{$pool->id}");

    $response->assertRedirect('/admin/loket')
        ->assertSessionHas('error', 'Pool tidak dapat dihapus karena masih terhubung dengan layanan, loket, atau antrian.');

    $this->assertDatabaseHas('queue_pools', ['id' => $pool->id]);
});

test('admin cannot delete pool with counters', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
    $pool = QueuePool::factory()->create();
    Counter::factory()->for($pool)->create();

    $response = $this->actingAs($admin)->delete("/admin/loket/pool/{$pool->id}");

    $response->assertRedirect('/admin/loket')
        ->assertSessionHas('error', 'Pool tidak dapat dihapus karena masih terhubung dengan layanan, loket, atau antrian.');

    $this->assertDatabaseHas('queue_pools', ['id' => $pool->id]);
});

test('admin cannot delete pool with tickets', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->create();
    QueueTicket::factory()->create([
        'queue_pool_id' => $pool->id,
        'service_id' => $service->id,
    ]);

    $response = $this->actingAs($admin)->delete("/admin/loket/pool/{$pool->id}");

    $response->assertRedirect('/admin/loket')
        ->assertSessionHas('error', 'Pool tidak dapat dihapus karena masih terhubung dengan layanan, loket, atau antrian.');

    $this->assertDatabaseHas('queue_pools', ['id' => $pool->id]);
});

test('admin can delete pool without relations', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
    $pool = QueuePool::factory()->create();

    $response = $this->actingAs($admin)->delete("/admin/loket/pool/{$pool->id}");

    $response->assertRedirect('/admin/loket')
        ->assertSessionHas('status', 'Pool antrian berhasil dihapus.');

    $this->assertDatabaseMissing('queue_pools', ['id' => $pool->id]);
});

test('admin cannot delete counter with active sessions', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
    $pool = QueuePool::factory()->create();
    $counter = Counter::factory()->for($pool)->create();

    // Create active counter session
    CounterSession::factory()->create([
        'counter_id' => $counter->id,
        'status' => 'open',
    ]);

    $response = $this->actingAs($admin)->delete("/admin/loket/{$counter->id}");

    $response->assertRedirect('/admin/loket')
        ->assertSessionHas('error', 'Loket tidak dapat dihapus karena memiliki sesi aktif.');

    $this->assertDatabaseHas('counters', ['id' => $counter->id]);
});

test('search filters counters by name', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
    $pool = QueuePool::factory()->create();
    $counter1 = Counter::factory()->for($pool)->create(['name' => 'Loket Umum 1']);
    $counter2 = Counter::factory()->for($pool)->create(['name' => 'Loket Khusus 2']);

    $response = $this->actingAs($admin)->get('/admin/loket?search=Umum');

    $response->assertOk()
        ->assertSee($counter1->name)
        ->assertDontSee($counter2->name);
});

test('search filters counters by code', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
    $pool = QueuePool::factory()->create();
    $counter1 = Counter::factory()->for($pool)->create(['code' => 'U1']);
    $counter2 = Counter::factory()->for($pool)->create(['code' => 'K2']);

    $response = $this->actingAs($admin)->get('/admin/loket?search=U1');

    $response->assertOk()
        ->assertSee($counter1->code)
        ->assertDontSee($counter2->code);
});

test('pagination returns 10 items per page', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
    $pool = QueuePool::factory()->create();

    // Create 15 counters
    Counter::factory()->for($pool)->count(15)->create();

    $response = $this->actingAs($admin)->get('/admin/loket');

    $response->assertOk();

    // First page should have 10 items
    $counters = $response->viewData('counters');
    expect($counters)->toHaveCount(10);
    expect($counters->total())->toBe(15);
});

test('counter tabs expose accessible state and keep inactive content cloaked', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get('/admin/loket')
        ->assertOk()
        ->assertSee('role="tablist"', false)
        ->assertSee('aria-label="Navigasi manajemen loket"', false)
        ->assertSee('id="counter-list-tab"', false)
        ->assertSee('aria-controls="counter-list-panel"', false)
        ->assertSee('id="counter-list-panel"', false)
        ->assertSee('role="tabpanel"', false)
        ->assertSee('x-bind:aria-selected', false)
        ->assertSee('x-on:keydown.right.prevent', false)
        ->assertSee('x-cloak', false);
});

test('counter mutation buttons rely on a single native form submission', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
    $pool = QueuePool::factory()->create(['name' => 'Pool Umum']);
    Counter::factory()->for($pool)->create(['name' => 'Loket Umum 1']);

    $this->actingAs($admin)
        ->get('/admin/loket')
        ->assertOk()
        ->assertDontSee('requestSubmit()', false);
});

test('counter management controls expose explicit and contextual labels', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
    $pool = QueuePool::factory()->create(['name' => 'Pool Umum']);
    Counter::factory()->for($pool)->create(['name' => 'Loket Umum 1']);

    $this->actingAs($admin)
        ->get('/admin/loket')
        ->assertOk()
        ->assertSee('aria-label="Cari loket"', false)
        ->assertSee('aria-label="Cari penugasan petugas"', false)
        ->assertSee('Nama pool')
        ->assertSee('Kode pool')
        ->assertSee('Kode huruf')
        ->assertSee('aria-label="Edit Loket Umum 1"', false)
        ->assertSee('aria-label="Hapus Loket Umum 1"', false)
        ->assertDontSee('Queue Pool');
});

test('counter overview reports a factual total and neutral empty state', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get('/admin/loket')
        ->assertOk()
        ->assertSee('0 Loket')
        ->assertSee('Tambahkan loket pertama')
        ->assertDontSee('langsung bisa menerima antrian');
});

test('pool table keeps a semantic head and scoped column headers', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
    QueuePool::factory()->create(['name' => 'Pool Umum']);

    $this->actingAs($admin)
        ->get('/admin/loket')
        ->assertOk()
        ->assertSee('<thead', false)
        ->assertSee('<th scope="col"', false)
        ->assertDontSee('scope="col"ead', false);
});
