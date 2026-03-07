<?php

use App\Actions\Queue\GenerateTicketNumber;
use App\Models\QueuePool;
use App\Models\QueueTicket;
use App\Models\Service;
use Carbon\CarbonImmutable;

test('first ticket in a pool for a date starts at sequence one', function () {
    $service = Service::factory()->create(['letter_code' => 'A']);
    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $service->update(['queue_pool_id' => $pool->id]);
    $serviceDate = CarbonImmutable::parse('2026-03-06');

    $result = app(GenerateTicketNumber::class)->handle($service, $pool, $serviceDate);

    expect($result['sequence_number'])->toBe(1)
        ->and($result['ticket_number'])->toBe('A0001');
});

test('next ticket in the same pool and date increments sequence', function () {
    $service = Service::factory()->create(['letter_code' => 'A']);
    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $service->update(['queue_pool_id' => $pool->id]);
    $serviceDate = CarbonImmutable::parse('2026-03-06');
    QueueTicket::factory()->create([
        'service_id' => $service->id,
        'queue_pool_id' => $pool->id,
        'service_date' => $serviceDate->toDateString(),
        'sequence_number' => 1,
        'ticket_number' => 'A0001',
    ]);

    $result = app(GenerateTicketNumber::class)->handle($service, $pool, $serviceDate);

    expect($result['sequence_number'])->toBe(2)
        ->and($result['ticket_number'])->toBe('A0002');
});

test('different pool on same date starts from one', function () {
    $firstPool = QueuePool::factory()->create(['code' => 'UMUM']);
    $secondPool = QueuePool::factory()->create(['code' => 'BAYAR']);
    $service1 = Service::factory()->create(['letter_code' => 'A', 'queue_pool_id' => $firstPool->id]);
    $service2 = Service::factory()->create(['letter_code' => 'B', 'queue_pool_id' => $secondPool->id]);
    $serviceDate = CarbonImmutable::parse('2026-03-06');

    QueueTicket::factory()->create([
        'service_id' => $service1->id,
        'queue_pool_id' => $firstPool->id,
        'service_date' => $serviceDate->toDateString(),
        'sequence_number' => 4,
        'ticket_number' => 'A0004',
    ]);

    $result = app(GenerateTicketNumber::class)->handle($service2, $secondPool, $serviceDate);

    expect($result['sequence_number'])->toBe(1)
        ->and($result['ticket_number'])->toBe('B0001');
});

test('same pool on a new date resets sequence to one', function () {
    $service = Service::factory()->create(['letter_code' => 'A']);
    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $service->update(['queue_pool_id' => $pool->id]);

    QueueTicket::factory()->create([
        'service_id' => $service->id,
        'queue_pool_id' => $pool->id,
        'service_date' => '2026-03-06',
        'sequence_number' => 8,
        'ticket_number' => 'A0008',
    ]);

    $result = app(GenerateTicketNumber::class)->handle($service, $pool, CarbonImmutable::parse('2026-03-07'));

    expect($result['sequence_number'])->toBe(1)
        ->and($result['ticket_number'])->toBe('A0001');
});
