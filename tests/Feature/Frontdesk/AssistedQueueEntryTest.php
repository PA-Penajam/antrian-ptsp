<?php

use App\Enums\UserRole;
use App\Models\QueuePool;
use App\Models\Service;
use App\Models\User;

test('frontdesk can create same day assisted ticket for any service', function () {
    $frontdesk = User::factory()->create([
        'role' => UserRole::Frontdesk->value,
        'email_verified_at' => now(),
    ]);

    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $service = Service::factory()->for($pool)->create(['is_active' => true, 'walk_in_enabled' => true]);

    $response = $this->actingAs($frontdesk)->post('/frontdesk/antrian', [
        'service_id' => $service->id,
        'channel' => 'assisted_same_day',
        'service_date' => now()->toDateString(),
        'visitor_name' => 'Pemohon Assisted Frontdesk',
        'visitor_identifier' => '7371BBBBBBBBBBBB',
        'visitor_phone' => '081222233344',
        'notes' => 'Input frontdesk',
    ]);

    $response->assertRedirect(route('frontdesk.queue.index'))
        ->assertSessionHas('created_ticket_id')
        ->assertSessionHas('status', 'Tiket berhasil dibuat.');

    $this->assertDatabaseHas('queue_tickets', [
        'service_id' => $service->id,
        'channel' => 'assisted_same_day',
        'status' => 'waiting',
    ]);
});

test('frontdesk rejects creating ticket when walk in is disabled for service', function () {
    $frontdesk = User::factory()->create([
        'role' => UserRole::Frontdesk->value,
        'email_verified_at' => now(),
    ]);

    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $service = Service::factory()->for($pool)->create([
        'is_active' => true,
        'walk_in_enabled' => false,
        'daily_quota' => 10,
    ]);

    $response = $this->actingAs($frontdesk)
        ->from(route('frontdesk.queue.index'))
        ->post('/frontdesk/antrian', [
            'service_id' => $service->id,
            'channel' => 'walk_in_kiosk',
            'service_date' => now()->toDateString(),
            'visitor_name' => 'Pemohon Walk In',
        ]);

    $response->assertRedirect(route('frontdesk.queue.index'))
        ->assertSessionHasErrors('service_id');
});

test('frontdesk rejects creating ticket when daily quota is full', function () {
    $frontdesk = User::factory()->create([
        'role' => UserRole::Frontdesk->value,
        'email_verified_at' => now(),
    ]);

    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $service = Service::factory()->for($pool)->create([
        'is_active' => true,
        'walk_in_enabled' => true,
        'daily_quota' => 1,
    ]);

    $this->actingAs($frontdesk)->post('/frontdesk/antrian', [
        'service_id' => $service->id,
        'channel' => 'assisted_same_day',
        'service_date' => now()->toDateString(),
        'visitor_name' => 'Pemohon Pertama',
    ])->assertRedirect(route('frontdesk.queue.index'));

    $response = $this->actingAs($frontdesk)
        ->from(route('frontdesk.queue.index'))
        ->post('/frontdesk/antrian', [
            'service_id' => $service->id,
            'channel' => 'assisted_same_day',
            'service_date' => now()->toDateString(),
            'visitor_name' => 'Pemohon Kedua',
        ]);

    $response->assertRedirect(route('frontdesk.queue.index'))
        ->assertSessionHasErrors('service_date');
});
