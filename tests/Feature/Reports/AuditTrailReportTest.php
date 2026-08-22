<?php

use App\Enums\UserRole;
use App\Models\Counter;
use App\Models\QueueActivity;
use App\Models\QueuePool;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\User;

test('monitor can open audit trail report page and filter by date and search', function () {
    $monitor = User::factory()->create([
        'role' => UserRole::Monitor->value,
        'email_verified_at' => now(),
    ]);

    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $service = Service::factory()->for($pool)->create(['name' => 'Informasi & Pengaduan']);
    $counter = Counter::factory()->for($pool)->create(['name' => 'Loket 2']);
    $officer = User::factory()->create(['name' => 'Budi Santoso']);

    $ticket = QueueTicket::factory()->for($service)->for($pool)->for($counter)->for($officer, 'creator')->create([
        'ticket_number' => 'A-042',
        'service_date' => now()->toDateString(),
    ]);

    QueueActivity::factory()->for($ticket)->for($officer)->for($counter)->create([
        'action' => 'called',
        'created_at' => now(),
    ]);

    $response = $this->actingAs($monitor)->get(route('laporan.audit', [
        'date' => now()->toDateString(),
        'search' => 'A-042',
    ]));

    $response->assertOk()
        ->assertSee('Audit Trail')
        ->assertSee('A-042')
        ->assertSee('Budi Santoso')
        ->assertSee('Informasi & Pengaduan')
        ->assertSee('Loket 2');
});
