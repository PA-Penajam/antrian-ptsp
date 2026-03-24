<?php

use App\Enums\UserRole;
use App\Models\Counter;
use App\Models\QueueActivity;
use App\Models\QueuePool;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('officer dashboard renders workstation modules and actions', function () {
    $user = User::factory()->create([
        'role' => UserRole::Officer->value,
    ]);
    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $service = Service::factory()->for($pool)->create(['name' => 'Pendaftaran']);
    $counter = Counter::factory()->for($pool)->create(['name' => 'Loket A']);
    $user->services()->attach($service);

    QueueTicket::factory()->for($service)->for($pool)->create([
        'ticket_number' => 'UMUM-0007',
        'sequence_number' => 7,
        'service_date' => now()->toDateString(),
        'status' => 'called',
        'counter_id' => $counter->id,
    ]);
    QueueTicket::factory()->for($service)->for($pool)->create([
        'ticket_number' => 'UMUM-0008',
        'sequence_number' => 8,
        'service_date' => now()->toDateString(),
        'status' => 'skipped',
        'counter_id' => null,
    ]);

    $completed = QueueTicket::factory()->for($service)->for($pool)->create([
        'ticket_number' => 'UMUM-0009',
        'sequence_number' => 9,
        'service_date' => now()->toDateString(),
        'status' => 'completed',
        'counter_id' => $counter->id,
    ]);

    QueueActivity::factory()->for($completed)->for($user)->for($counter)->create([
        'action' => 'ticket_completed',
    ]);

    actingAs($user);

    $response = get(route('dashboard'));

    $response->assertOk()
        ->assertSee('Modul Panggilan Petugas')
        ->assertSee('Tiket Aktif')
        ->assertSee('Panggil Berikutnya')
        ->assertSee('Panggil Ulang')
        ->assertSee('Lewati')
        ->assertSee('Selesai')
        ->assertSee('Daftar Skip Layanan')
        ->assertSee('Kinerja Hari Ini')
        ->assertSee('UMUM-0007')
        ->assertSee('UMUM-0008');
});

test('monitor dashboard renders analytics summary', function () {
    $monitor = User::factory()->create([
        'role' => UserRole::Monitor->value,
    ]);

    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $service = Service::factory()->for($pool)->create(['name' => 'Informasi']);
    $officer = User::factory()->create([
        'role' => UserRole::Officer->value,
        'name' => 'Petugas A',
    ]);
    $officer->services()->attach($service);

    QueueTicket::factory()->for($service)->for($pool)->create([
        'service_date' => now()->toDateString(),
        'status' => 'waiting',
        'counter_id' => null,
    ]);

    $served = QueueTicket::factory()->for($service)->for($pool)->create([
        'service_date' => now()->toDateString(),
        'status' => 'completed',
    ]);
    QueueActivity::factory()->for($served)->for($officer)->create([
        'action' => 'ticket_completed',
    ]);

    actingAs($monitor);

    get(route('dashboard'))
        ->assertOk()
        ->assertSee('Ringkasan Monitoring')
        ->assertSee('Total Dilayani Hari Ini')
        ->assertSee('Backlog per Layanan')
        ->assertSee('Distribusi Petugas x Layanan')
        ->assertSee('Petugas A')
        ->assertSee('Informasi');
});

test('admin dashboard renders health widgets and shortcuts', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
    ]);
    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $service = Service::factory()->for($pool)->create(['name' => 'Konsultasi']);

    QueueTicket::factory()->for($service)->for($pool)->create([
        'service_date' => now()->toDateString(),
        'channel' => 'online_booking',
        'status' => 'completed',
    ]);
    QueueTicket::factory()->for($service)->for($pool)->create([
        'service_date' => now()->toDateString(),
        'channel' => 'online_booking',
        'status' => 'cancelled',
    ]);

    actingAs($admin);

    get(route('dashboard'))
        ->assertOk()
        ->assertSee('Health Aplikasi')
        ->assertSee('Booking Berhasil')
        ->assertSee('Booking Gagal')
        ->assertSee('Akses Cepat')
        ->assertSee('/admin/layanan')
        ->assertSee('/admin/loket')
        ->assertSee('/admin/users')
        ->assertSee('/admin/users?tab=roles')
        ->assertSee('Distribusi per Layanan');
});
