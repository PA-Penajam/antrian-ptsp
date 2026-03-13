<?php

use App\Models\QueueTicket;

it('does not expose ticket by ticket number without service_date', function () {
    $ticket = QueueTicket::factory()->create();

    $response = $this->getJson("/api/queue/ticket/{$ticket->ticket_number}");

    $response->assertNotFound();
});

it('does not expose visitor PII in public ticket lookup', function () {
    $ticket = QueueTicket::factory()->create([
        'visitor_name' => 'Nama Rahasia',
        'visitor_identifier' => '1234567890123456',
        'visitor_phone' => '081234567890',
    ]);

    $response = $this->getJson('/api/queue/lookup?'.http_build_query([
        'ticket_number' => $ticket->ticket_number,
        'service_date' => $ticket->service_date->format('Y-m-d'),
    ]));

    $response->assertOk()
        ->assertJsonMissing(['visitor_identifier' => '1234567890123456'])
        ->assertJsonMissing(['visitor_phone' => '081234567890']);
});
