<?php

use Illuminate\Support\Facades\Schema;

test('queue domain tables exist', function () {
    expect(Schema::hasTable('queue_pools'))->toBeTrue()
        ->and(Schema::hasTable('services'))->toBeTrue()
        ->and(Schema::hasTable('counters'))->toBeTrue()
        ->and(Schema::hasTable('queue_tickets'))->toBeTrue()
        ->and(Schema::hasTable('counter_sessions'))->toBeTrue()
        ->and(Schema::hasTable('queue_activities'))->toBeTrue();
});

test('queue tickets table has the expected operational columns', function () {
    $expectedColumns = [
        'service_id',
        'queue_pool_id',
        'counter_id',
        'created_by',
        'channel',
        'ticket_number',
        'sequence_number',
        'service_date',
        'status',
        'checked_in_at',
        'called_at',
        'started_at',
        'completed_at',
        'cancelled_at',
    ];

    $columns = Schema::getColumnListing('queue_tickets');

    foreach ($expectedColumns as $column) {
        expect($columns)->toContain($column);
    }
});
