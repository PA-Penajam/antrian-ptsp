<?php

use App\Models\Counter;
use App\Models\QueuePool;
use App\Models\QueueTicket;
use App\Models\Service;

use function Pest\Laravel\get;

test('display page is public and shows current call with history', function () {
    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $service = Service::factory()->for($pool)->create();
    $counter = Counter::factory()->for($pool)->create(['name' => 'Loket Umum 1']);

    QueueTicket::factory()->for($service)->for($pool)->for($counter)->create([
        'ticket_number' => 'UMUM-0001',
        'status' => 'completed',
        'called_at' => now()->subMinutes(5),
    ]);

    QueueTicket::factory()->for($service)->for($pool)->for($counter)->create([
        'ticket_number' => 'UMUM-0002',
        'status' => 'called',
        'called_at' => now()->subMinute(),
    ]);

    $response = get('/display');

    $response->assertOk()
        ->assertSee('Display Antrian PTSP')
        ->assertSee('Sedang Dipanggil')
        ->assertSee('wire:poll.5000ms', false)
        ->assertSee('UMUM-0002')
        ->assertSee('Loket Umum 1')
        ->assertSee('Riwayat Panggilan')
        ->assertSee('UMUM-0001');
});
