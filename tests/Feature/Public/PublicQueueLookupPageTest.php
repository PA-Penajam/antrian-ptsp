<?php

use App\Models\QueuePool;
use App\Models\QueueTicket;
use App\Models\Service;

test('public user can open queue lookup page', function () {
    $response = $this->get('/antrian/cek');

    $response->assertOk()
        ->assertSee('Cek Status Antrian');
});

test('public user can lookup ticket by ticket number and service date', function () {
    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $service = Service::factory()->for($pool)->create();
    $ticket = QueueTicket::factory()->for($service)->for($pool)->create([
        'ticket_number' => 'UMUM-0012',
        'service_date' => '2026-03-10',
        'status' => 'waiting',
    ]);

    $response = $this->get('/antrian/cek?ticket_number=UMUM-0012&service_date=2026-03-10');

    $response->assertOk()
        ->assertSee('Hasil Pencarian')
        ->assertSee($ticket->ticket_number)
        ->assertSee('waiting');
});
