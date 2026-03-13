<?php

use App\Enums\QueueStatus;
use App\Models\QueuePool;
use App\Models\QueueTicket;
use App\Models\Service;

test('public user can open queue lookup page', function () {
    $response = $this->get('/antrian/cek');

    $response->assertOk()
        ->assertSee('Cek Status Antrian')
        ->assertSee('Nomor Antrian')
        ->assertSee('Tanggal Layanan')
        ->assertSee('Cari Tiket');
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
        ->assertSee('UMUM-0012')
        ->assertSee($ticket->visitor_name)
        ->assertSee($service->name)
        ->assertSee(QueueStatus::Waiting->label());
});

test('lookup page shows not found message for invalid ticket', function () {
    $response = $this->get('/antrian/cek?ticket_number=INVALID&service_date=2026-03-10');

    $response->assertOk()
        ->assertSee('Tiket Tidak Ditemukan');
});

test('lookup page shows status-specific guidance for each queue status', function () {
    $pool = QueuePool::factory()->create(['code' => 'TST']);
    $service = Service::factory()->for($pool)->create();

    $ticket = QueueTicket::factory()->for($service)->for($pool)->create([
        'ticket_number' => 'TST-0001',
        'service_date' => '2026-03-10',
        'status' => QueueStatus::Called,
    ]);

    $response = $this->get('/antrian/cek?ticket_number=TST-0001&service_date=2026-03-10');

    $response->assertOk()
        ->assertSee(QueueStatus::Called->label());
});
