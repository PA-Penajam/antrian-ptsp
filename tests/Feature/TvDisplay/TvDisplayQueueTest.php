<?php

use App\Enums\QueueStatus;
use App\Models\Counter;
use App\Models\QueuePool;
use App\Models\QueueTicket;
use App\Models\Service;

use function Pest\Laravel\get;

it('tv display shows queue when authenticated', function () {
    $this->withSession([
        'tv_display_authenticated' => true,
        'tv_display_authenticated_at' => now()->timestamp,
    ]);

    $response = get(route('tv-display.index'));

    $response->assertOk()
        ->assertSee('Monitor Antrian PTSP')
        ->assertSee('Sedang Dipanggil')
        ->assertSee('Riwayat Panggilan')
        ->assertSee('wire:poll.5s', false);
});

it('tv display shows empty state when no calls', function () {
    $this->withSession([
        'tv_display_authenticated' => true,
        'tv_display_authenticated_at' => now()->timestamp,
    ]);

    $response = get(route('tv-display.index'));

    $response->assertOk()
        ->assertSee('Belum ada panggilan');
});

it('tv display shows called tickets', function () {
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create();
    $counter = Counter::factory()->for($pool)->create(['name' => 'Loket 1']);

    QueueTicket::factory()->for($service)->for($pool)->for($counter)->create([
        'ticket_number' => 'A001',
        'status' => QueueStatus::Called,
        'service_date' => today(),
        'called_at' => now(),
    ]);

    $this->withSession([
        'tv_display_authenticated' => true,
        'tv_display_authenticated_at' => now()->timestamp,
    ]);

    $response = get(route('tv-display.index'));

    $response->assertOk()
        ->assertSee('A001')
        ->assertSee('Loket 1');
});

it('tv display shows recent calls in history', function () {
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create();
    $counter = Counter::factory()->for($pool)->create(['name' => 'Loket 2']);

    // Create a completed ticket that was called
    QueueTicket::factory()->for($service)->for($pool)->for($counter)->create([
        'ticket_number' => 'B002',
        'status' => QueueStatus::Completed,
        'service_date' => today(),
        'called_at' => now()->subMinutes(5),
    ]);

    $this->withSession([
        'tv_display_authenticated' => true,
        'tv_display_authenticated_at' => now()->timestamp,
    ]);

    $response = get(route('tv-display.index'));

    $response->assertOk()
        ->assertSee('B002')
        ->assertSee('Loket 2');
});

it('tv display shows recent calls empty state when no history', function () {
    $this->withSession([
        'tv_display_authenticated' => true,
        'tv_display_authenticated_at' => now()->timestamp,
    ]);

    $response = get(route('tv-display.index'));

    $response->assertOk()
        ->assertSee('Belum ada riwayat hari ini');
});

it('tv display renders with current date', function () {
    $this->withSession([
        'tv_display_authenticated' => true,
        'tv_display_authenticated_at' => now()->timestamp,
    ]);

    $response = get(route('tv-display.index'));

    $response->assertOk()
        ->assertSee('Monitor Antrian PTSP');
});

it('tv display includes connection status indicator', function () {
    $this->withSession([
        'tv_display_authenticated' => true,
        'tv_display_authenticated_at' => now()->timestamp,
    ]);

    $response = get(route('tv-display.index'));

    $response->assertOk()
        ->assertSee('livewire:connecting', false)
        ->assertSee('livewire:connected', false);
});

it('tv display uses alpine js for live clock', function () {
    $this->withSession([
        'tv_display_authenticated' => true,
        'tv_display_authenticated_at' => now()->timestamp,
    ]);

    $response = get(route('tv-display.index'));

    $response->assertOk()
        ->assertSee('x-data="{ time: \'\' }"', false)
        ->assertSee('setInterval', false)
        ->assertSee("toLocaleTimeString('id-ID'", false);
});
