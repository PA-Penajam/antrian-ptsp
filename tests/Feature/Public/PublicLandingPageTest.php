<?php

use App\Models\Service;
use Illuminate\Support\Facades\View;

use function Pest\Laravel\get;

test('public user can open landing page and see primary guidance', function () {
    $response = get('/');

    $response->assertSuccessful()
        ->assertSeeText('Sistem Antrian PTSP')
        ->assertSeeText('Ambil Nomor Antrian')
        ->assertSeeText('Cek Status Antrian')
        ->assertSeeText('Lihat Papan Antrian')
        ->assertSeeText('Panduan Pengunjung')
        ->assertSeeTextInOrder([
            'Pilih Layanan',
            'Isi Data Diri',
            'Tunjukkan Nomor Antrian',
        ])
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

    View::share('services', collect([$service]));

    $response = get('/');

    $response->assertSuccessful()
        ->assertSeeText('Katalog Layanan')
        ->assertSeeText($service->name)
        ->assertSeeText($service->description)
        ->assertSeeText('KTP')
        ->assertSeeText('Dokumen pendukung')
        ->assertSeeText('Online')
        ->assertSeeText('Walk-in')
        ->assertSeeText('Kuota/hari: 25');
});
