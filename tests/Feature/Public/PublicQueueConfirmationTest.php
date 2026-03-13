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

    $response->assertOk()
        ->assertSee('Konfirmasi Antrian')
        ->assertSee('ABC123')
        ->assertSee('John Doe')
        ->assertSee($service->name)
        ->assertSee($ticket->status->label());
});

test('confirmation page has print button and navigation links', function () {
    $service = Service::factory()->create();
    $ticket = QueueTicket::factory()->create([
        'service_id' => $service->id,
        'status' => QueueStatus::Booked,
    ]);

    $response = $this->get(route('queue.confirmation', $ticket));

    $response->assertOk()
        ->assertSee('Cetak Tiket')
        ->assertSee('Cek Status Antrian')
        ->assertSee('Kembali ke Beranda');
});
