<?php

use App\Enums\QueueStatus;
use App\Enums\UserRole;
use App\Models\Counter;
use App\Models\QueuePool;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\User;

test('guest is redirected from internal PTSP pages', function () {
    $counter = Counter::factory()->for(QueuePool::factory())->create();

    $this->get('/frontdesk/antrian')->assertRedirect(route('login'));
    $this->get("/petugas/loket/{$counter->id}")->assertRedirect(route('login'));
    $this->get('/laporan/antrian')->assertRedirect(route('login'));
    $this->get('/admin/layanan')->assertRedirect(route('login'));
    $this->get('/admin/users')->assertRedirect(route('login'));
});

test('authenticated users without matching role are forbidden', function () {
    $counter = Counter::factory()->for(QueuePool::factory())->create();
    $monitor = User::factory()->create([
        'role' => UserRole::Monitor->value,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($monitor);

    $this->get('/frontdesk/antrian')->assertForbidden();
    $this->get("/petugas/loket/{$counter->id}")->assertForbidden();
    $this->get('/admin/layanan')->assertForbidden();
    $this->get('/admin/users')->assertForbidden();
});

test('users with matching roles can access PTSP pages', function () {
    $counter = Counter::factory()->for(QueuePool::factory())->create();
    $frontdesk = User::factory()->create([
        'role' => UserRole::Frontdesk->value,
        'email_verified_at' => now(),
    ]);
    $officer = User::factory()->create([
        'role' => UserRole::Officer->value,
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

    $this->actingAs($frontdesk)->get('/frontdesk/antrian')->assertOk();
    $this->actingAs($officer)->get("/petugas/loket/{$counter->id}")->assertOk();
    $this->actingAs($monitor)->get('/laporan/antrian')->assertOk();
    $this->actingAs($admin)->get('/admin/layanan')->assertOk();
    $this->actingAs($admin)->get('/admin/users')->assertOk();
});

test('officer cannot claim ticket from unassigned service', function () {
    $officer = User::factory()->create([
        'role' => UserRole::Officer->value,
        'email_verified_at' => now(),
    ]);

    $pool = QueuePool::factory()->create();
    $restrictedService = Service::factory()->for($pool)->create();
    $counter = Counter::factory()->for($pool)->create();
    $ticket = QueueTicket::factory()->for($restrictedService)->for($pool)->create([
        'status' => QueueStatus::Waiting,
        'counter_id' => null,
    ]);

    $this->actingAs($officer)
        ->post("/petugas/loket/{$counter->id}/call-next")
        ->assertOk()
        ->assertSee('Tidak ada antrean');

    expect($ticket->fresh()->status)->toBe(QueueStatus::Waiting);
});
