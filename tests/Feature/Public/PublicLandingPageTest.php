<?php

use App\Enums\QueueStatus;
use App\Livewire\PublicQueueMonitor;
use App\Models\QueuePool;
use App\Models\QueueTicket;
use App\Models\Service;
use Livewire\Livewire;

use function Pest\Laravel\get;

test('public user can open landing page and see primary guidance', function () {
    $response = get('/');

    $response->assertSuccessful()
        ->assertSeeText('Sistem Antrian PTSP')
        ->assertSeeText('Ambil Nomor Antrian')
        ->assertSeeText('Cek Status Antrian')
        ->assertSeeText('Panduan Pengunjung')
        ->assertSeeTextInOrder([
            'Pilih Layanan',
            'Isi Data Diri',
            'Tunjukkan Nomor Antrian',
        ])
        ->assertSee(route('flux.script'), false)
        ->assertDontSee('/flux/flux.js', false)
        ->assertSeeText(config('institution.operating_hours'));
});

test('landing page renders service catalog details when services are available', function () {
    $service = new Service([
        'name' => 'Konsultasi Informasi',
        'description' => 'Pendampingan awal untuk kebutuhan administrasi pengunjung.',
        'requirements' => "KTP\nDokumen pendukung",
        'daily_quota' => 25,
        'booking_enabled' => true,
        'walk_in_enabled' => true,
    ]);

    $html = view('welcome', [
        'services' => collect([$service]),
    ])->render();

    expect(strip_tags($html))
        ->toContain('Katalog Layanan')
        ->toContain($service->name)
        ->toContain($service->description)
        ->toContain('KTP')
        ->toContain('Dokumen pendukung')
        ->toContain('Online')
        ->toContain('Walk-in');
});

test('landing page renders live queue monitor with active calling ticket', function () {
    $response = get('/');

    $response->assertSuccessful()
        ->assertSeeText('Pantauan Antrian Hari Ini')
        ->assertSeeText('Total Tiket Terdaftar')
        ->assertSeeText('Sedang Menunggu')
        ->assertSeeText('Selesai Dilayani');
});

test('public queue monitor livewire component renders and performs quick lookup', function () {
    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $service = Service::factory()->for($pool)->create(['name' => 'Pendaftaran Perkara']);

    $ticket1 = QueueTicket::factory()->for($service)->for($pool)->create([
        'ticket_number' => 'UMUM-0001',
        'service_date' => today(),
        'sequence_number' => 1,
        'status' => QueueStatus::Waiting,
    ]);

    $ticket2 = QueueTicket::factory()->for($service)->for($pool)->create([
        'ticket_number' => 'UMUM-0002',
        'service_date' => today(),
        'sequence_number' => 2,
        'status' => QueueStatus::Waiting,
    ]);

    Livewire::test(PublicQueueMonitor::class)
        ->assertSee('Pantauan Antrian Hari Ini')
        ->assertSee('Total Tiket Terdaftar')
        ->set('quickTicketNumber', 'UMUM-0002')
        ->call('searchTicket')
        ->assertSee('UMUM-0002')
        ->assertSee('Sisa Antrian Di Depan')
        ->assertSee('Orang')
        ->assertSee('Ada 1 antrian sebelum giliran Anda')
        ->call('clearLookup')
        ->assertDontSee('Sisa Antrian Di Depan');
});

test('public queue monitor displays error when ticket is not found', function () {
    Livewire::test(PublicQueueMonitor::class)
        ->set('quickTicketNumber', 'NONEXISTENT')
        ->call('searchTicket')
        ->assertSee('tidak ditemukan untuk antrian hari ini');
});
