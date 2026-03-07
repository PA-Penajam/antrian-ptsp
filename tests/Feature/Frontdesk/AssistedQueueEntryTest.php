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

    $response->assertOk()
        ->assertSee('Tiket Berhasil Dibuat')
        ->assertSee('waiting');

    $this->assertDatabaseHas('queue_tickets', [
        'service_id' => $service->id,
        'channel' => 'assisted_same_day',
        'status' => 'waiting',
    ]);
});
