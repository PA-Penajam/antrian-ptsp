<?php

use App\Actions\Queue\GenerateTicketNumber;
use App\Models\QueuePool;
use App\Models\QueueTicket;
use Carbon\CarbonImmutable;

test('first ticket in a pool for a date starts at sequence one', function () {
    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $serviceDate = CarbonImmutable::parse('2026-03-06');

    $result = app(GenerateTicketNumber::class)->handle($pool, $serviceDate);

    expect($result['sequence_number'])->toBe(1)
        ->and($result['ticket_number'])->toBe('UMUM-0001');
});

test('next ticket in the same pool and date increments sequence', function () {
    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $serviceDate = CarbonImmutable::parse('2026-03-06');
    QueueTicket::factory()->create([
        'queue_pool_id' => $pool->id,
        'service_date' => $serviceDate->toDateString(),
        'sequence_number' => 1,
        'ticket_number' => 'UMUM-0001',
    ]);

    $result = app(GenerateTicketNumber::class)->handle($pool, $serviceDate);

    expect($result['sequence_number'])->toBe(2)
        ->and($result['ticket_number'])->toBe('UMUM-0002');
});

test('different pool on same date starts from one', function () {
    $firstPool = QueuePool::factory()->create(['code' => 'UMUM']);
    $secondPool = QueuePool::factory()->create(['code' => 'BAYAR']);
    $serviceDate = CarbonImmutable::parse('2026-03-06');

    QueueTicket::factory()->create([
        'queue_pool_id' => $firstPool->id,
        'service_date' => $serviceDate->toDateString(),
        'sequence_number' => 4,
        'ticket_number' => 'UMUM-0004',
    ]);

    $result = app(GenerateTicketNumber::class)->handle($secondPool, $serviceDate);

    expect($result['sequence_number'])->toBe(1)
        ->and($result['ticket_number'])->toBe('BAYAR-0001');
});

test('same pool on a new date resets sequence to one', function () {
    $pool = QueuePool::factory()->create(['code' => 'UMUM']);

    QueueTicket::factory()->create([
        'queue_pool_id' => $pool->id,
        'service_date' => '2026-03-06',
        'sequence_number' => 8,
        'ticket_number' => 'UMUM-0008',
    ]);

    $result = app(GenerateTicketNumber::class)->handle($pool, CarbonImmutable::parse('2026-03-07'));

    expect($result['sequence_number'])->toBe(1)
        ->and($result['ticket_number'])->toBe('UMUM-0001');
});
