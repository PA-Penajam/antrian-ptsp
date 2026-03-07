<?php

use App\Enums\QueueStatus;
use App\Models\QueueTicket;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('confirmation page displays ticket details', function () {
    $service = Service::factory()->create();
    $ticket = QueueTicket::factory()->create([
        'service_id' => $service->id,
        'ticket_number' => 'ABC123',
        'status' => QueueStatus::Waiting,
        'visitor_name' => 'John Doe',
        'sequence_number' => 5,
    ]);

    $url = route('queue.confirmation', $ticket);
    $response = $this->get($url);

    $response->assertOk();
    $response->assertSee('Konfirmasi Antrian');
    $response->assertSee('ABC123');
    $response->assertSee('John Doe');
});
