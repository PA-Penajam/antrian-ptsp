<?php

use App\Models\QueuePool;
use App\Models\Service;

beforeEach(function () {
    $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $this->service = Service::factory()->for($pool)->create([
        'is_active' => true,
        'booking_enabled' => true,
    ]);
});

it('rejects booking with past service_date', function () {
    $response = $this->post('/antrian', [
        'service_id' => $this->service->id,
        'service_date' => now()->subDay()->toDateString(),
        'visitor_name' => 'Test',
        'visitor_identifier' => 'ID123',
        'visitor_phone' => '08123456789',
    ]);

    $response->assertSessionHasErrors(['service_date']);
});

it('rejects booking with service_date more than 14 days ahead', function () {
    $response = $this->post('/antrian', [
        'service_id' => $this->service->id,
        'service_date' => now()->addDays(15)->toDateString(),
        'visitor_name' => 'Test',
        'visitor_identifier' => 'ID123',
        'visitor_phone' => '08123456789',
    ]);

    $response->assertSessionHasErrors(['service_date']);
});

it('rejects booking with notes longer than 1000 characters', function () {
    $response = $this->post('/antrian', [
        'service_id' => $this->service->id,
        'service_date' => now()->nextWeekday()->toDateString(),
        'visitor_name' => 'Test',
        'visitor_identifier' => 'ID123',
        'visitor_phone' => '08123456789',
        'notes' => str_repeat('x', 1001),
    ]);

    $response->assertSessionHasErrors(['notes']);
});
