<?php

use App\Actions\Queue\CreateQueueTicket;
use App\Enums\QueueStatus;
use App\Models\QueuePool;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;

test('online booking creates a booked ticket and logs activity', function () {
    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $service = Service::factory()->for($pool)->create();
    $creator = User::factory()->create();

    $ticket = app(CreateQueueTicket::class)->handle([
        'service_id' => $service->id,
        'channel' => 'online_booking',
        'service_date' => CarbonImmutable::parse('2026-03-06'),
        'visitor_name' => 'Pemohon Online',
        'visitor_identifier' => '7371XXXXXXXXXXXX',
        'visitor_phone' => '08123456789',
        'notes' => 'Online booking test',
        'created_by' => $creator->id,
    ]);

    expect($ticket->status)->toBe(QueueStatus::Booked)
        ->and($ticket->queue_pool_id)->toBe($pool->id)
        ->and($ticket->channel)->toBe('online_booking');

    $this->assertDatabaseHas('queue_activities', [
        'queue_ticket_id' => $ticket->id,
        'action' => 'ticket_created',
    ]);
});

test('assisted same day creates waiting ticket and logs activity', function () {
    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $service = Service::factory()->for($pool)->create();
    $creator = User::factory()->create();

    $ticket = app(CreateQueueTicket::class)->handle([
        'service_id' => $service->id,
        'channel' => 'assisted_same_day',
        'service_date' => CarbonImmutable::parse('2026-03-06'),
        'visitor_name' => 'Pemohon Assisted',
        'visitor_identifier' => '7371YYYYYYYYYYYY',
        'visitor_phone' => '081299988877',
        'notes' => 'Assisted entry test',
        'created_by' => $creator->id,
    ]);

    expect($ticket->status)->toBe(QueueStatus::Waiting)
        ->and($ticket->queue_pool_id)->toBe($pool->id)
        ->and($ticket->channel)->toBe('assisted_same_day');

    $this->assertDatabaseHas('queue_activities', [
        'queue_ticket_id' => $ticket->id,
        'action' => 'ticket_created',
    ]);
});

test('walk in kiosk creates waiting ticket and logs activity', function () {
    $pool = QueuePool::factory()->create(['code' => 'POSBAKUM']);
    $service = Service::factory()->for($pool)->create();
    $creator = User::factory()->create();

    $ticket = app(CreateQueueTicket::class)->handle([
        'service_id' => $service->id,
        'channel' => 'walk_in_kiosk',
        'service_date' => CarbonImmutable::parse('2026-03-06'),
        'visitor_name' => 'Pemohon Walkin',
        'visitor_identifier' => '7371ZZZZZZZZZZZZ',
        'visitor_phone' => '081277766655',
        'notes' => 'Walk-in kiosk test',
        'created_by' => $creator->id,
    ]);

    expect($ticket->status)->toBe(QueueStatus::Waiting)
        ->and($ticket->queue_pool_id)->toBe($pool->id)
        ->and($ticket->channel)->toBe('walk_in_kiosk');

    $this->assertDatabaseHas('queue_activities', [
        'queue_ticket_id' => $ticket->id,
        'action' => 'ticket_created',
    ]);
});
