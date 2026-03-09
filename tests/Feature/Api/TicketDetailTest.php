<?php

use App\Enums\QueueStatus;
use App\Models\QueueTicket;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can get ticket detail by ticket_number', function () {
    $ticket = QueueTicket::factory()->create([
        'ticket_number' => 'TKT001',
        'service_date' => Carbon::today(),
        'status' => QueueStatus::Waiting,
    ]);

    $response = $this->getJson('/api/queue/ticket/TKT001');

    $response->assertSuccessful()
        ->assertJsonPath('data.ticket_number', 'TKT001')
        ->assertJsonPath('data.status', 'waiting')
        ->assertJsonPath('data.service_date', Carbon::today()->toDateString());
});

test('ticket detail returns 404 for non-existent ticket', function () {
    $response = $this->getJson('/api/queue/ticket/NONEXISTENT');

    $response->assertNotFound()
        ->assertJson(['message' => 'Tiket tidak ditemukan']);
});

test('ticket detail returns ticket regardless of service_date parameter', function () {
    $ticket = QueueTicket::factory()->create([
        'ticket_number' => 'TKT001',
        'service_date' => Carbon::today(),
    ]);

    $response = $this->getJson('/api/queue/ticket/TKT001');

    $response->assertSuccessful()
        ->assertJsonPath('data.ticket_number', 'TKT001')
        ->assertJsonPath('data.service_date', Carbon::today()->toDateString());
});

test('ticket detail includes service relationship', function () {
    $ticket = QueueTicket::factory()->create([
        'ticket_number' => 'TKT001',
        'service_date' => Carbon::today(),
    ]);

    $response = $this->getJson('/api/queue/ticket/TKT001');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'ticket_number',
                'service_date',
                'service' => [
                    'id',
                    'name',
                    'code',
                ],
            ],
        ]);
});

test('ticket detail includes queue position for waiting tickets', function () {
    $ticket1 = QueueTicket::factory()->create([
        'ticket_number' => 'TKT001',
        'service_date' => Carbon::today(),
        'status' => QueueStatus::Waiting,
        'sequence_number' => 1,
    ]);

    $ticket2 = QueueTicket::factory()->create([
        'ticket_number' => 'TKT002',
        'service_date' => Carbon::today(),
        'status' => QueueStatus::Waiting,
        'sequence_number' => 2,
        'queue_pool_id' => $ticket1->queue_pool_id,
    ]);

    $response = $this->getJson('/api/queue/ticket/TKT002');

    $response->assertSuccessful()
        ->assertJsonPath('data.queue_position', 2);
});
