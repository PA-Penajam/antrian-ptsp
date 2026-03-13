<?php

use App\Enums\QueueStatus;
use App\Models\QueueTicket;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can lookup ticket by ticket_number and service_date', function () {
    $ticket = QueueTicket::factory()->create([
        'ticket_number' => 'TKT001',
        'service_date' => Carbon::today(),
        'status' => QueueStatus::Waiting,
    ]);

    $response = $this->getJson('/api/queue/lookup?ticket_number=TKT001&service_date='.Carbon::today()->toDateString());

    $response->assertSuccessful()
        ->assertJsonPath('data.ticket_number', 'TKT001')
        ->assertJsonPath('data.status', 'waiting')
        ->assertJsonPath('data.service_date', Carbon::today()->toDateString());
});

test('lookup returns 404 when ticket not found', function () {
    $response = $this->getJson('/api/queue/lookup?ticket_number=NONEXISTENT&service_date='.Carbon::today()->toDateString());

    $response->assertNotFound()
        ->assertJson(['message' => 'Tiket tidak ditemukan']);
});

test('lookup returns 422 when missing params', function () {
    // Missing ticket_number
    $response = $this->getJson('/api/queue/lookup?service_date='.Carbon::today()->toDateString());

    $response->assertStatus(422);

    // Missing service_date
    $response = $this->getJson('/api/queue/lookup?ticket_number=TKT001');

    $response->assertStatus(422);
});

test('lookup returns queue position for waiting tickets', function () {
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

    $response = $this->getJson('/api/queue/lookup?ticket_number=TKT002&service_date='.Carbon::today()->toDateString());

    $response->assertSuccessful()
        ->assertJsonPath('data.queue_position', 2);
});

test('lookup returns null position for booked tickets', function () {
    $ticket = QueueTicket::factory()->create([
        'ticket_number' => 'TKT001',
        'service_date' => Carbon::today(),
        'status' => QueueStatus::Booked,
    ]);

    $response = $this->getJson('/api/queue/lookup?ticket_number=TKT001&service_date='.Carbon::today()->toDateString());

    $response->assertSuccessful()
        ->assertJsonPath('data.queue_position', null);
});
