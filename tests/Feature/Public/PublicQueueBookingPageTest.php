<?php

use App\Models\QueuePool;
use App\Models\Service;

test('public user can open booking page and see active services', function () {
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create([
        'name' => 'Pendaftaran',
        'is_active' => true,
    ]);

    $response = $this->get('/antrian');

    $response->assertOk()
        ->assertSee('Ambil Antrian PTSP')
        ->assertSee($service->name);
});

test('public user can submit booking and receive confirmation', function () {
    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $service = Service::factory()->for($pool)->create([
        'is_active' => true,
        'booking_enabled' => true,
    ]);

    $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

    $response = $this->post('/antrian', [
        'service_id' => $service->id,
        'service_date' => now()->nextWeekday()->toDateString(),
        'visitor_name' => 'Pemohon Publik',
        'visitor_identifier' => '7371AAAAAAAAAAAA',
        'visitor_phone' => '081234567890',
        'notes' => 'Booking dari test',
    ]);

    $response->assertRedirect()
        ->assertSessionHasNoErrors();

    $this->assertDatabaseCount('queue_tickets', 1);
    $this->assertDatabaseHas('queue_tickets', [
        'service_id' => $service->id,
        'channel' => 'online_booking',
        'status' => 'booked',
    ]);
});

test('booking page shows required form fields', function () {
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create([
        'name' => 'Pendaftaran',
        'is_active' => true,
        'booking_enabled' => true,
    ]);

    $response = $this->get('/antrian');

    $response->assertOk()
        ->assertSee('Layanan')
        ->assertSee('Tanggal Layanan')
        ->assertSee('Nama Lengkap')
        ->assertSee('Nomor Identitas')
        ->assertSee('Nomor Telepon / WhatsApp')
        ->assertSee('Catatan Tambahan')
        ->assertSee($service->name);
});
