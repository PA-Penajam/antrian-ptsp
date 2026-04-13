<?php

use App\Enums\UserRole;
use App\Models\Counter;
use App\Models\CounterSession;
use App\Models\QueueActivity;
use App\Models\QueuePool;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\User;

test('monitor can open report page and filter by date', function () {
    $monitor = User::factory()->create([
        'role' => UserRole::Monitor->value,
        'email_verified_at' => now(),
    ]);

    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $serviceA = Service::factory()->for($pool)->create(['name' => 'Pendaftaran']);
    $serviceB = Service::factory()->for($pool)->create(['name' => 'Pengambilan Produk Hukum']);
    $counter = Counter::factory()->for($pool)->create(['name' => 'Loket Umum 1']);
    $officer = User::factory()->create(['name' => 'Petugas PTSP']);

    CounterSession::factory()->for($counter)->for($officer)->create();

    $ticket1 = QueueTicket::factory()->for($serviceA)->for($pool)->for($counter)->for($officer, 'creator')->create([
        'service_date' => '2026-03-10',
        'status' => 'completed',
    ]);
    $ticket2 = QueueTicket::factory()->for($serviceB)->for($pool)->for($counter)->for($officer, 'creator')->create([
        'service_date' => '2026-03-10',
        'status' => 'waiting',
    ]);
    $ticket3 = QueueTicket::factory()->for($serviceA)->for($pool)->for($counter)->for($officer, 'creator')->create([
        'service_date' => '2026-03-11',
        'status' => 'completed',
    ]);

    QueueActivity::factory()->for($ticket1)->for($officer)->for($counter)->create([
        'action' => 'ticket_completed',
    ]);
    QueueActivity::factory()->for($ticket3)->for($officer)->for($counter)->create([
        'action' => 'ticket_completed',
    ]);

    $response = $this->actingAs($monitor)->get('/laporan/antrian?from=2026-03-10&to=2026-03-10');

    $response->assertOk()
        ->assertSee('Laporan Antrian')
        ->assertSee('Pendaftaran')
        ->assertSee('Pengambilan Produk Hukum')
        ->assertSee('Loket Umum 1')
        ->assertSee('Petugas PTSP')
        ->assertSee('Distribusi Petugas x Layanan');
});
