<?php

use App\Enums\QueueStatus;
use App\Models\Counter;
use App\Models\QueuePool;
use App\Models\QueueTicket;
use App\Models\Service;

it('returns institution data', function () {
    /** @var \Tests\TestCase $this */
    $response = $this->getJson('/api/institution');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'name',
            'address',
            'phone',
            'operating_hours',
            'logo_path',
        ]);
});

it('returns active services', function () {
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->create([
        'queue_pool_id' => $pool->id,
        'is_active' => true,
        'sort_order' => 1,
    ]);

    /** @var \Tests\TestCase $this */
    $response = $this->getJson('/api/services');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'name', 'code', 'slug', 'description', 'requirements', 'booking_enabled', 'daily_quota', 'remaining_quota',
                ],
            ],
        ]);
});

it('creates a new booking', function () {
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->create([
        'queue_pool_id' => $pool->id,
        'is_active' => true,
        'booking_enabled' => true,
    ]);

    /** @var \Tests\TestCase $this */
    $response = $this->postJson('/api/queue/booking', [
        'service_id' => $service->id,
        'service_date' => now()->nextWeekday()->format('Y-m-d'),
        'visitor_name' => 'John Doe',
        'visitor_phone' => '08123456789',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => [
                'ticket_number',
                'service_date',
                'visitor_name',
                'status',
                'status_label',
                'service',
            ],
        ]);
});

it('looks up a ticket', function () {
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->create(['queue_pool_id' => $pool->id]);
    $counter = Counter::factory()->create();
    $ticket = QueueTicket::factory()->create([
        'service_id' => $service->id,
        'queue_pool_id' => $pool->id,
        'counter_id' => $counter->id,
        'status' => QueueStatus::Booked,
    ]);

    /** @var \Tests\TestCase $this */
    $response = $this->getJson('/api/queue/lookup?ticket_number='.$ticket->ticket_number.'&service_date='.$ticket->service_date->format('Y-m-d'));

    $response->assertStatus(200)
        ->assertJsonFragment([
            'ticket_number' => $ticket->ticket_number,
        ]);
});
