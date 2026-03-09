<?php

use App\Enums\QueueStatus;
use App\Models\QueueTicket;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->withoutMiddleware(ThrottleRequests::class));

test('can create booking and returns 201', function () {
    $service = Service::factory()->create(['is_active' => true, 'booking_enabled' => true]);
    $serviceDate = Carbon::now()->nextWeekday()->toDateString();

    $response = $this->postJson('/api/queue/booking', [
        'service_id' => $service->id,
        'service_date' => $serviceDate,
        'visitor_name' => fake()->name(),
        'visitor_identifier' => fake()->numerify('################'),
        'visitor_phone' => fake()->phoneNumber(),
        'notes' => fake()->paragraph(),
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.status', 'booked')
        ->assertJsonPath('data.service.id', $service->id)
        ->assertJsonPath('data.ticket_number', fn ($value) => ! empty($value));
});

test('cannot book inactive service returns 422', function () {
    $service = Service::factory()->create(['is_active' => false, 'booking_enabled' => true]);
    $serviceDate = Carbon::now()->nextWeekday()->toDateString();

    $response = $this->postJson('/api/queue/booking', [
        'service_id' => $service->id,
        'service_date' => $serviceDate,
        'visitor_name' => fake()->name(),
        'visitor_identifier' => fake()->numerify('################'),
        'visitor_phone' => fake()->phoneNumber(),
        'notes' => fake()->paragraph(),
    ]);

    $response->assertStatus(422);
});

test('cannot book when booking disabled returns 422', function () {
    $service = Service::factory()->create(['is_active' => true, 'booking_enabled' => false]);
    $serviceDate = Carbon::now()->nextWeekday()->toDateString();

    $response = $this->postJson('/api/queue/booking', [
        'service_id' => $service->id,
        'service_date' => $serviceDate,
        'visitor_name' => fake()->name(),
        'visitor_identifier' => fake()->numerify('################'),
        'visitor_phone' => fake()->phoneNumber(),
        'notes' => fake()->paragraph(),
    ]);

    $response->assertStatus(422);
});

test('cannot book past date returns 422', function () {
    $service = Service::factory()->create(['is_active' => true, 'booking_enabled' => true]);
    $serviceDate = Carbon::yesterday()->toDateString();

    $response = $this->postJson('/api/queue/booking', [
        'service_id' => $service->id,
        'service_date' => $serviceDate,
        'visitor_name' => fake()->name(),
        'visitor_identifier' => fake()->numerify('################'),
        'visitor_phone' => fake()->phoneNumber(),
        'notes' => fake()->paragraph(),
    ]);

    $response->assertStatus(422);
});

test('quota exceeded returns 422', function () {
    $service = Service::factory()->create([
        'is_active' => true,
        'booking_enabled' => true,
        'daily_quota' => 1,
    ]);
    $serviceDate = Carbon::now()->nextWeekday()->toDateString();

    QueueTicket::factory()->create([
        'service_id' => $service->id,
        'service_date' => $serviceDate,
        'status' => QueueStatus::Waiting,
    ]);

    $response = $this->postJson('/api/queue/booking', [
        'service_id' => $service->id,
        'service_date' => $serviceDate,
        'visitor_name' => fake()->name(),
        'visitor_identifier' => fake()->numerify('################'),
        'visitor_phone' => fake()->phoneNumber(),
        'notes' => fake()->paragraph(),
    ]);

    $response->assertStatus(422);
});

test('missing required fields returns 422', function () {
    $response = $this->postJson('/api/queue/booking', []);

    $response->assertStatus(422);
});
