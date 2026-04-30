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
        ->assertSee('name="service_id"', false);
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
        'service_id' => $serviceA->id,
    ]);

    $response->assertRedirect('/admin/users');

    $officer->refresh();

    expect($officer->role)->toBe(UserRole::Officer)
        ->and($officer->services()->pluck('services.id')->all())
        ->toBe([$serviceA->id]);
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

test('admin can create user', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($admin)->post('/admin/users', [
        'name' => 'User Baru',
        'email' => 'newuser@example.com',
        'role' => UserRole::Officer->value,
        'password' => 'password123',
        'services' => [],
    ]);

    $response->assertRedirect('/admin/users');

    $this->assertDatabaseHas('users', [
        'name' => 'User Baru',
        'email' => 'newuser@example.com',
        'role' => UserRole::Officer->value,
    ]);
});

test('admin can delete user without active tickets', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
    $userToDelete = User::factory()->create([
        'role' => UserRole::Officer->value,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($admin)->delete("/admin/users/{$userToDelete->id}");

    $response->assertRedirect('/admin/users');
    $response->assertSessionHas('status');

    $this->assertDatabaseMissing('users', [
        'id' => $userToDelete->id,
    ]);
});

test('admin cannot delete themselves', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($admin)->delete("/admin/users/{$admin->id}");

    $response->assertRedirect('/admin/users');
    $response->assertSessionHas('error', 'Anda tidak dapat menghapus akun sendiri.');

    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
    ]);
});

test('admin cannot delete user with active tickets', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);
    $userWithTickets = User::factory()->create([
        'role' => UserRole::Officer->value,
        'email_verified_at' => now(),
    ]);

    // Create an active ticket created by this user
    $pool = \App\Models\QueuePool::factory()->create(['code' => 'UMUM']);
    $service = \App\Models\Service::factory()->for($pool)->create(['name' => 'Pendaftaran']);

    \App\Models\QueueTicket::factory()->create([
        'service_id' => $service->id,
        'queue_pool_id' => $pool->id,
        'created_by' => $userWithTickets->id,
        'status' => \App\Enums\QueueStatus::Waiting,
        'ticket_number' => 'A001',
        'sequence_number' => 1,
    ]);

    $response = $this->actingAs($admin)->delete("/admin/users/{$userWithTickets->id}");

    $response->assertRedirect('/admin/users');
    $response->assertSessionHas('error', 'User tidak dapat dihapus karena memiliki antrian aktif.');

    $this->assertDatabaseHas('users', [
        'id' => $userWithTickets->id,
    ]);
});

test('tabbed UI renders all 3 tabs', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get('/admin/users');

    $response->assertOk()
        ->assertSee('Semua Users')
        ->assertSee('Role & Izin', false)
        ->assertSee('Tambah User');
});

test('empty state shown when no other users exist', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get('/admin/users');

    $response->assertOk()
        ->assertSee('Belum ada user selain Anda');
});

test('admin can create officer with services', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);

    $pool = \App\Models\QueuePool::factory()->create(['code' => 'UMUM']);
    $serviceA = \App\Models\Service::factory()->for($pool)->create(['name' => 'Pendaftaran']);

    $response = $this->actingAs($admin)->post('/admin/users', [
        'name' => 'Officer Dengan Layanan',
        'email' => 'officerservices@example.com',
        'role' => UserRole::Officer->value,
        'password' => 'password123',
        'service_id' => $serviceA->id,
    ]);

    $response->assertRedirect('/admin/users');

    $officer = User::where('email', 'officerservices@example.com')->first();
    expect($officer->services)->toHaveCount(1)
        ->and($officer->services->first()->id)->toBe($serviceA->id);
});

test('admin can update user to non-officer and detach services', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);

    $pool = \App\Models\QueuePool::factory()->create(['code' => 'UMUM']);
    $serviceA = \App\Models\Service::factory()->for($pool)->create(['name' => 'Pendaftaran']);

    $officer = User::factory()->create([
        'role' => UserRole::Officer->value,
        'email_verified_at' => now(),
    ]);

    // Attach service initially
    $officer->services()->attach($serviceA->id);

    $response = $this->actingAs($admin)->put("/admin/users/{$officer->id}", [
        'name' => $officer->name,
        'email' => $officer->email,
        'role' => UserRole::Monitor->value,
        'services' => [$serviceA->id], // Intentionally send services even when monitor to test detaching
    ]);

    $response->assertRedirect('/admin/users');

    $officer->refresh();
    expect($officer->role)->toBe(UserRole::Monitor)
        ->and($officer->services)->toBeEmpty();
});
