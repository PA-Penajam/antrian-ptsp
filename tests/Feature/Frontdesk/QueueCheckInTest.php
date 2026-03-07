<?php

use App\Enums\UserRole;
use App\Models\QueuePool;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\User;

test('frontdesk can check in booked ticket to waiting status', function () {
    $frontdesk = User::factory()->create([
        'role' => UserRole::Frontdesk->value,
        'email_verified_at' => now(),
    ]);
    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $service = Service::factory()->for($pool)->create();
    $ticket = QueueTicket::factory()->for($service)->for($pool)->create([
        'status' => 'booked',
        'channel' => 'online_booking',
        'checked_in_at' => null,
    ]);

    $response = $this->actingAs($frontdesk)->post('/frontdesk/antrian/check-in', [
        'ticket_id' => $ticket->id,
    ]);

    $response->assertOk()
        ->assertSee('Check-in Berhasil')
        ->assertSee('waiting');

    $this->assertDatabaseHas('queue_tickets', [
        'id' => $ticket->id,
        'status' => 'waiting',
    ]);

    expect($ticket->fresh()->checked_in_at)->not->toBeNull();

    $this->assertDatabaseHas('queue_activities', [
        'queue_ticket_id' => $ticket->id,
        'action' => 'ticket_checked_in',
        'user_id' => $frontdesk->id,
    ]);
});
