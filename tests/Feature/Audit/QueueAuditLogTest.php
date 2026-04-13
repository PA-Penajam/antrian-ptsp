<?php

use App\Actions\Queue\CallNextTicket;
use App\Actions\Queue\CheckInQueueTicket;
use App\Actions\Queue\CompleteTicket;
use App\Actions\Queue\CreateQueueTicket;
use App\Actions\Queue\RecallTicket;
use App\Actions\Queue\SkipTicket;
use App\Models\Counter;
use App\Models\QueueActivity;
use App\Models\QueuePool;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\User;
use App\Support\Dashboard\PetugasStats;

test('queue lifecycle actions create audit entries with actor context', function () {
    $actor = User::factory()->create();
    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $service = Service::factory()->for($pool)->create();
    $counter = Counter::factory()->for($pool)->create();

    $ticket = app(CreateQueueTicket::class)->handle([
        'service_id' => $service->id,
        'channel' => 'online_booking',
        'service_date' => today(),
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

    $completedActivity = QueueActivity::query()
        ->where('queue_ticket_id', $calledTicket->id)
        ->where('action', 'ticket_completed')
        ->first();

    expect($completedActivity)->not->toBeNull()
        ->and($completedActivity?->meta['from_status'] ?? null)->toBe('called')
        ->and($completedActivity?->meta['to_status'] ?? null)->toBe('completed')
        ->and($completedActivity?->meta['service_id'] ?? null)->toBe($service->id);
});

test('petugas stats provide daily served action counts and service distribution', function () {
    $actor = User::factory()->create();
    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $serviceA = Service::factory()->for($pool)->create(['name' => 'Pendaftaran']);
    $serviceB = Service::factory()->for($pool)->create(['name' => 'Pengaduan']);
    $counter = Counter::factory()->for($pool)->create();

    $ticketA = QueueTicket::factory()->for($serviceA)->for($pool)->create([
        'status' => 'called',
        'service_date' => now()->toDateString(),
        'counter_id' => $counter->id,
    ]);
    $ticketB = QueueTicket::factory()->for($serviceB)->for($pool)->create([
        'status' => 'called',
        'service_date' => now()->toDateString(),
        'counter_id' => $counter->id,
    ]);

    app(SkipTicket::class)->handle($ticketA, $counter, $actor->id);
    app(RecallTicket::class)->handle($ticketB, $counter, $actor->id);
    app(CompleteTicket::class)->handle($ticketB->fresh(), $counter, $actor->id);

    $stats = app(PetugasStats::class)->build($actor, now()->toDateString());

    expect($stats['served_today'])->toBe(1)
        ->and($stats['action_counts']['skipped'])->toBe(1)
        ->and($stats['action_counts']['recalled'])->toBe(1)
        ->and($stats['action_counts']['completed'])->toBe(1)
        ->and($stats['service_distribution']['Pengaduan'] ?? 0)->toBeGreaterThanOrEqual(1);
});
