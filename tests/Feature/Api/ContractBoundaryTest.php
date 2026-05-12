<?php

use App\Enums\QueueStatus;
use App\Models\QueueTicket;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

/**
 * Contract boundary tests for the public API consumed by the antrian-public
 * CI4 gateway. These tests lock the field-length limits the gateway relies
 * on when performing its lightweight local validation.
 */
uses(RefreshDatabase::class);

beforeEach(fn () => $this->withoutMiddleware(ThrottleRequests::class));

test('booking accepts visitor fields at the documented upper boundaries', function () {
    $service = Service::factory()->create(['is_active' => true, 'booking_enabled' => true]);
    $serviceDate = Carbon::now()->nextWeekday()->toDateString();

    $response = $this->postJson('/api/queue/booking', [
        'service_id' => $service->id,
        'service_date' => $serviceDate,
        'visitor_name' => str_repeat('a', 255),
        'visitor_identifier' => str_repeat('9', 64),
        'visitor_phone' => str_repeat('0', 30),
        'notes' => str_repeat('n', 1000),
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.status', 'booked')
        ->assertJsonPath('data.service.id', $service->id);
});

test('booking rejects visitor_name above 255 characters', function () {
    $service = Service::factory()->create(['is_active' => true, 'booking_enabled' => true]);
    $serviceDate = Carbon::now()->nextWeekday()->toDateString();

    $response = $this->postJson('/api/queue/booking', [
        'service_id' => $service->id,
        'service_date' => $serviceDate,
        'visitor_name' => str_repeat('a', 256),
        'visitor_identifier' => 'IDENT',
        'visitor_phone' => '08123456789',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['visitor_name']);
});

test('booking rejects visitor_phone above 30 characters', function () {
    $service = Service::factory()->create(['is_active' => true, 'booking_enabled' => true]);
    $serviceDate = Carbon::now()->nextWeekday()->toDateString();

    $response = $this->postJson('/api/queue/booking', [
        'service_id' => $service->id,
        'service_date' => $serviceDate,
        'visitor_name' => 'Budi',
        'visitor_phone' => str_repeat('0', 31),
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['visitor_phone']);
});

test('booking rejects visitor_identifier above 64 characters', function () {
    $service = Service::factory()->create(['is_active' => true, 'booking_enabled' => true]);
    $serviceDate = Carbon::now()->nextWeekday()->toDateString();

    $response = $this->postJson('/api/queue/booking', [
        'service_id' => $service->id,
        'service_date' => $serviceDate,
        'visitor_name' => 'Budi',
        'visitor_identifier' => str_repeat('9', 65),
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['visitor_identifier']);
});

test('time endpoint returns timestamp datetime and configured timezone', function () {
    $response = $this->getJson('/api/time');

    $response->assertOk()
        ->assertJsonStructure(['timestamp', 'datetime', 'timezone'])
        ->assertJsonPath('timezone', config('app.timezone'));

    expect($response->json('timestamp'))->toBeInt();
});

test('ticket-by-id returns ticket shape when encrypted id is valid', function () {
    $ticket = QueueTicket::factory()->create([
        'ticket_number' => 'TKT123',
        'service_date' => Carbon::today(),
        'status' => QueueStatus::Waiting,
    ]);

    $response = $this->getJson('/api/queue/ticket-by-id/'.encrypt($ticket->id));

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'id',
                'ticket_number',
                'service_date',
                'status',
                'status_label',
                'queue_position',
                'counter_name',
                'checked_in_at',
                'called_at',
                'completed_at',
            ],
        ])
        ->assertJsonPath('data.ticket_number', 'TKT123');
});

test('ticket-by-id returns 404 when encrypted id is invalid', function () {
    $response = $this->getJson('/api/queue/ticket-by-id/not-a-real-cipher');

    $response->assertStatus(404)
        ->assertJson(['message' => 'Tiket tidak ditemukan']);
});
