<?php

use App\Actions\Queue\CallNextTicket;
use App\Actions\Queue\CheckInQueueTicket;
use App\Actions\Queue\CompleteTicket;
use App\Actions\Queue\CreateQueueTicket;
use App\Actions\Queue\RecallTicket;
use App\Models\Counter;
use App\Models\QueuePool;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;

test('queue lifecycle actions create audit entries with actor context', function () {
    $actor = User::factory()->create();
    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $service = Service::factory()->for($pool)->create();
    $counter = Counter::factory()->for($pool)->create();

    $ticket = app(CreateQueueTicket::class)->handle([
        'service_id' => $service->id,
        'channel' => 'online_booking',
        'service_date' => CarbonImmutable::parse('2026-03-10'),
        'visitor_name' => 'Pemohon Audit',
        'visitor_identifier' => '7371CCCCCCCCCCCC',
        'visitor_phone' => '081233344455',
        'notes' => 'Audit',
        'created_by' => $actor->id,
    ]);

    app(CheckInQueueTicket::class)->handle($ticket, $actor->id);

    $called = app(CallNextTicket::class)->handle($counter, $actor->id);
    expect($called)->not->toBeNull();

    /** @var QueueTicket $calledTicket */
    $calledTicket = $called;
    app(RecallTicket::class)->handle($calledTicket, $counter, $actor->id);
    app(CompleteTicket::class)->handle($calledTicket, $counter, $actor->id);

    $actions = $calledTicket->fresh()->activities()
        ->orderBy('id')
        ->pluck('action')
        ->all();

    expect($actions)->toContain('ticket_created')
        ->and($actions)->toContain('ticket_checked_in')
        ->and($actions)->toContain('ticket_called')
        ->and($actions)->toContain('ticket_recalled')
        ->and($actions)->toContain('ticket_completed');

    $this->assertDatabaseHas('queue_activities', [
        'queue_ticket_id' => $calledTicket->id,
        'action' => 'ticket_completed',
        'user_id' => $actor->id,
        'counter_id' => $counter->id,
    ]);
});
