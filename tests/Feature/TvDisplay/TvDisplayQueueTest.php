<?php

use App\Enums\QueueStatus;
use App\Models\Counter;
use App\Models\QueuePool;
use App\Models\QueueTicket;
use App\Models\Service;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\get;

it('tv display shows queue when authenticated', function () {
    $this->withSession([
        'tv_display_authenticated' => true,
        'tv_display_authenticated_at' => now()->timestamp,
    ]);

    $response = get(route('tv-display.index'));

    $response->assertOk()
        ->assertSee('Pengadilan Agama')
        ->assertSee('Sedang Dipanggil')
        ->assertSee('Riwayat')
        ->assertSee('echo:public-queue,TicketCalled', false);
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
        ->assertSee('Pengadilan Agama');
});

it('tv display includes connection status indicator', function () {
    $this->withSession([
        'tv_display_authenticated' => true,
        'tv_display_authenticated_at' => now()->timestamp,
    ]);

    $response = get(route('tv-display.index'));

    $response->assertOk()
        ->assertSee('online.window', false)
        ->assertSee('offline.window', false);
});

it('tv display reuses a persistent audio element for tts playback', function () {
    $this->withSession([
        'tv_display_authenticated' => true,
        'tv_display_authenticated_at' => now()->timestamp,
    ]);

    $response = get(route('tv-display.index'));

    $response->assertOk()
        ->assertSee('x-ref="ttsAudio"', false)
        ->assertSee('isSamsungTv', false)
        ->assertSee('vid.muted = !this.audioUnlocked', false)
        ->assertSee('this.videoPausedForTts = true', false)
        ->assertSee('x-on:play-tts.window="playTts($event.detail.text)"', false)
        ->assertDontSee('new Audio(data.audio_url)', false);
});

it('tv display renders a persistent contained video player when videos exist', function () {
    Storage::fake('public');
    Cache::forget('tv-display:videos');
    Storage::disk('public')->put('videos/demo.mp4', 'fake-video');

    $this->withSession([
        'tv_display_authenticated' => true,
        'tv_display_authenticated_at' => now()->timestamp,
    ]);

    $response = get(route('tv-display.index'));

    $response->assertOk()
        ->assertSee('demo.mp4', false)
        ->assertSee('wire:ignore', false)
        ->assertSee('x-ref="videoPlayer"', false)
        ->assertSee('object-contain', false)
        ->assertDontSee('<template x-if="hasVideos">', false);

    Cache::forget('tv-display:videos');
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
