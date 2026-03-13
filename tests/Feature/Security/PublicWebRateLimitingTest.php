<?php

use App\Models\QueuePool;
use App\Models\Service;

it('rate limits public booking submissions', function () {
    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $service = Service::factory()->for($pool)->create([
        'is_active' => true,
        'booking_enabled' => true,
    ]);

    $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

    for ($i = 0; $i < 10; $i++) {
        $this->post('/antrian', [
            'service_id' => $service->id,
            'service_date' => now()->addDay()->toDateString(),
            'visitor_name' => "Visitor {$i}",
            'visitor_identifier' => "ID{$i}",
            'visitor_phone' => "08123456789{$i}",
        ]);
    }

    $response = $this->post('/antrian', [
        'service_id' => $service->id,
        'service_date' => now()->addDay()->toDateString(),
        'visitor_name' => 'One More',
        'visitor_identifier' => 'IDMORE',
        'visitor_phone' => '08199999999',
    ]);

    $response->assertStatus(429);
});
