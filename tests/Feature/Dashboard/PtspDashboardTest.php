<?php

use App\Models\QueuePool;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\User;

test('authenticated user sees PTSP dashboard summary for today', function () {
    $user = User::factory()->create();
    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $service = Service::factory()->for($pool)->create();

    QueueTicket::factory()
        ->for($service)
        ->for($pool)
        ->count(2)
        ->sequence(
            ['sequence_number' => 1],
            ['sequence_number' => 2],
        )
        ->create([
            'service_date' => now()->toDateString(),
            'status' => 'waiting',
        ]);
    QueueTicket::factory()->for($service)->for($pool)->create([
        'sequence_number' => 3,
        'service_date' => now()->toDateString(),
        'status' => 'called',
    ]);
    QueueTicket::factory()->for($service)->for($pool)->create([
        'sequence_number' => 4,
        'service_date' => now()->toDateString(),
        'status' => 'completed',
    ]);
    QueueTicket::factory()->for($service)->for($pool)->create([
        'sequence_number' => 5,
        'service_date' => now()->toDateString(),
        'status' => 'cancelled',
    ]);

    QueueTicket::factory()->for($service)->for($pool)->create([
        'service_date' => now()->subDay()->toDateString(),
        'status' => 'waiting',
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()
        ->assertSee('Ringkasan PTSP Hari Ini')
        ->assertSee('Menunggu')
        ->assertSee('2')
        ->assertSee('Dipanggil')
        ->assertSee('1')
        ->assertSee('Selesai')
        ->assertSee('Batal')
        ->assertSee('Total');
});
