<?php

use App\Models\Counter;
use App\Models\QueueActivity;
use App\Models\QueuePool;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\User;

test('service belongs to a queue pool', function () {
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create();

    expect($service->queuePool)->toBeInstanceOf(QueuePool::class)
        ->and($service->queuePool->is($pool))->toBeTrue();
});

test('counter belongs to a queue pool', function () {
    $pool = QueuePool::factory()->create();
    $counter = Counter::factory()->for($pool)->create();

    expect($counter->queuePool)->toBeInstanceOf(QueuePool::class)
        ->and($counter->queuePool->is($pool))->toBeTrue();
});

test('queue ticket belongs to service, pool, and counter', function () {
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create();
    $counter = Counter::factory()->for($pool)->create();
    $creator = User::factory()->create();
    $ticket = QueueTicket::factory()
        ->for($service)
        ->for($pool)
        ->for($counter)
        ->for($creator, 'creator')
        ->create();

    expect($ticket->service)->toBeInstanceOf(Service::class)
        ->and($ticket->queuePool)->toBeInstanceOf(QueuePool::class)
        ->and($ticket->counter)->toBeInstanceOf(Counter::class)
        ->and($ticket->creator)->toBeInstanceOf(User::class);
});

test('queue ticket has many activity records', function () {
    $ticket = QueueTicket::factory()->create();
    QueueActivity::factory()->for($ticket)->count(2)->create();

    expect($ticket->activities)->toHaveCount(2)
        ->and($ticket->activities->first())->toBeInstanceOf(QueueActivity::class);
});
