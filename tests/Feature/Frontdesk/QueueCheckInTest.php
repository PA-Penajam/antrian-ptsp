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
        'ticket_number' => $ticket->ticket_number,
    ]);

    $response->assertRedirect(route('frontdesk.queue.index'))
        ->assertSessionHas('checked_in_ticket_id')
        ->assertSessionHas('status', 'Check-in tiket berhasil diproses.');

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

test('frontdesk receives validation error when checking in non-booked ticket', function () {
    $frontdesk = User::factory()->create([
        'role' => UserRole::Frontdesk->value,
        'email_verified_at' => now(),
    ]);
    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $service = Service::factory()->for($pool)->create();
    $ticket = QueueTicket::factory()->for($service)->for($pool)->create([
        'status' => 'waiting',
        'channel' => 'assisted_same_day',
        'checked_in_at' => null,
    ]);

    $response = $this->actingAs($frontdesk)
        ->from(route('frontdesk.queue.index'))
        ->post('/frontdesk/antrian/check-in', [
            'ticket_number' => strtolower($ticket->ticket_number),
        ]);

    $response->assertRedirect(route('frontdesk.queue.index'))
        ->assertSessionHasErrors('ticket_number');
});
