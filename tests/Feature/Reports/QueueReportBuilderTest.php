<?php

use App\Enums\QueueStatus;
use App\Models\Counter;
use App\Models\CounterSession;
use App\Models\QueueActivity;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\User;
use App\Support\Reports\QueueReportBuilder;

it('returns correct aggregations from database queries', function () {
    $service = Service::factory()->create();
    $counter = Counter::factory()->create();
    $user = User::factory()->create();

    CounterSession::factory()->create([
        'counter_id' => $counter->id,
        'user_id' => $user->id,
    ]);

    $tickets = QueueTicket::factory()->count(5)->create([
        'service_id' => $service->id,
        'counter_id' => $counter->id,
        'created_by' => $user->id,
        'service_date' => today(),
        'status' => QueueStatus::Completed,
    ]);

    foreach ($tickets as $ticket) {
        QueueActivity::factory()->create([
            'queue_ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'counter_id' => $counter->id,
            'action' => 'ticket_completed',
        ]);
    }

    $builder = new QueueReportBuilder;
    $result = $builder->build(today()->toDateString(), today()->toDateString());

    expect($result['by_service'])->toHaveKey($service->name)
        ->and($result['by_service'][$service->name])->toBe(5)
        ->and($result['by_counter'])->toHaveKey($counter->name)
        ->and($result['by_counter'][$counter->name])->toBe(5)
        ->and($result['by_status'])->toHaveKey(QueueStatus::Completed->value)
        ->and($result['by_status'][QueueStatus::Completed->value])->toBe(5)
        ->and($result['by_officer'])->toHaveKey($user->name)
        ->and($result['by_officer'][$user->name])->toBe(5);
});

it('returns empty arrays when no tickets exist', function () {
    $builder = new QueueReportBuilder;
    $result = $builder->build(today()->toDateString(), today()->toDateString());

    expect($result['by_service'])->toBeEmpty()
        ->and($result['by_counter'])->toBeEmpty()
        ->and($result['by_status'])->toBeEmpty()
        ->and($result['by_officer'])->toBeEmpty()
        ->and($result['officer_service_distribution'])->toBeEmpty();
});
