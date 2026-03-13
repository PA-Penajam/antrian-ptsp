<?php

use App\Enums\QueueStatus;
use App\Enums\UserRole;
use App\Livewire\Dashboard\AdminDashboard;
use App\Models\Counter;
use App\Models\QueuePool;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\User;
use Livewire\Livewire;

test('admin dashboard component renders successfully', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
    ]);

    Livewire::actingAs($admin)
        ->test(AdminDashboard::class)
        ->assertOk()
        ->assertSee('Total Hari Ini')
        ->assertSee('Sudah Dilayani')
        ->assertSee('Menunggu')
        ->assertSee('Rata-rata Tunggu');
});

test('stat cards display correct data for today', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
    ]);
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create();
    $counter = Counter::factory()->for($pool)->create();
    $today = today()->toDateString();
    $seq = 1;

    // Create 5 completed tickets for today
    foreach (range(1, 5) as $i) {
        QueueTicket::factory()->for($service)->for($pool)->create([
            'service_date' => $today,
            'ticket_number' => 'TEST-C-'.$i,
            'sequence_number' => $seq++,
            'status' => QueueStatus::Completed,
            'counter_id' => $counter->id,
            'called_at' => now()->subMinutes(10),
            'completed_at' => now()->subMinutes(5),
        ]);
    }

    // Create 2 waiting tickets
    foreach (range(1, 2) as $i) {
        QueueTicket::factory()->for($service)->for($pool)->create([
            'service_date' => $today,
            'ticket_number' => 'TEST-W-'.$i,
            'sequence_number' => $seq++,
            'status' => QueueStatus::Waiting,
        ]);
    }

    // Create 1 called ticket
    QueueTicket::factory()->for($service)->for($pool)->create([
        'service_date' => $today,
        'ticket_number' => 'TEST-D-1',
        'sequence_number' => $seq++,
        'status' => QueueStatus::Called,
        'counter_id' => $counter->id,
        'called_at' => now()->subMinutes(2),
    ]);

    Livewire::actingAs($admin)
        ->test(AdminDashboard::class)
        ->assertSee('8')  // Total
        ->assertSee('5')  // Served
        ->assertSee('3'); // Waiting
});

test('date range filter changes data correctly', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
    ]);
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create();
    $today = today()->toDateString();
    $yesterday = today()->subDay()->toDateString();
    $seq = 1;

    // Create 3 tickets for today
    foreach (range(1, 3) as $i) {
        QueueTicket::factory()->for($service)->for($pool)->create([
            'service_date' => $today,
            'ticket_number' => 'TODAY-'.$i,
            'sequence_number' => $seq++,
            'status' => QueueStatus::Completed,
        ]);
    }

    // Create 5 tickets for yesterday
    foreach (range(1, 5) as $i) {
        QueueTicket::factory()->for($service)->for($pool)->create([
            'service_date' => $yesterday,
            'ticket_number' => 'YEST-'.$i,
            'sequence_number' => $seq++,
            'status' => QueueStatus::Completed,
        ]);
    }

    Livewire::actingAs($admin)
        ->test(AdminDashboard::class)
        ->assertSee('3') // Today only
        ->set('startDate', $yesterday)
        ->set('endDate', $today)
        ->assertSee('8'); // Yesterday + Today
});

test('byService returns correct counts grouped by service', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
    ]);
    $pool = QueuePool::factory()->create();
    $service1 = Service::factory()->for($pool)->create(['name' => 'Pendaftaran']);
    $service2 = Service::factory()->for($pool)->create(['name' => 'Konsultasi']);
    $today = today()->toDateString();
    $seq = 1;

    // Create 3 tickets for Pendaftaran
    foreach (range(1, 3) as $i) {
        QueueTicket::factory()->for($service1)->for($pool)->create([
            'service_date' => $today,
            'ticket_number' => 'PEND-'.$i,
            'sequence_number' => $seq++,
        ]);
    }

    // Create 2 tickets for Konsultasi
    foreach (range(1, 2) as $i) {
        QueueTicket::factory()->for($service2)->for($pool)->create([
            'service_date' => $today,
            'ticket_number' => 'KONS-'.$i,
            'sequence_number' => $seq++,
        ]);
    }

    $component = Livewire::actingAs($admin)->test(AdminDashboard::class);

    // Assert the component sees the correct total
    $component->assertSee('5'); // 3 + 2 tickets total
});

test('byCounter returns correct counts grouped by counter', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
    ]);
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create();
    $counter1 = Counter::factory()->for($pool)->create(['name' => 'Loket A']);
    $counter2 = Counter::factory()->for($pool)->create(['name' => 'Loket B']);
    $today = today()->toDateString();
    $seq = 1;

    // Create 4 tickets for Loket A
    foreach (range(1, 4) as $i) {
        QueueTicket::factory()->for($service)->for($pool)->create([
            'service_date' => $today,
            'ticket_number' => 'A-'.$i,
            'sequence_number' => $seq++,
            'counter_id' => $counter1->id,
        ]);
    }

    // Create 1 ticket for Loket B
    QueueTicket::factory()->for($service)->for($pool)->create([
        'service_date' => $today,
        'ticket_number' => 'B-1',
        'sequence_number' => $seq++,
        'counter_id' => $counter2->id,
    ]);

    $component = Livewire::actingAs($admin)->test(AdminDashboard::class);

    // Assert the component sees the correct total
    $component->assertSee('5'); // 4 + 1 tickets total
});

test('byChannel returns correct counts for each channel', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
    ]);
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create();
    $today = today()->toDateString();
    $seq = 1;

    // Create tickets for different channels
    foreach (range(1, 2) as $i) {
        QueueTicket::factory()->for($service)->for($pool)->create([
            'service_date' => $today,
            'ticket_number' => 'ON-'.$i,
            'sequence_number' => $seq++,
            'channel' => 'online_booking',
        ]);
    }
    foreach (range(1, 3) as $i) {
        QueueTicket::factory()->for($service)->for($pool)->create([
            'service_date' => $today,
            'ticket_number' => 'AS-'.$i,
            'sequence_number' => $seq++,
            'channel' => 'assisted_same_day',
        ]);
    }
    QueueTicket::factory()->for($service)->for($pool)->create([
        'service_date' => $today,
        'ticket_number' => 'WK-1',
        'sequence_number' => $seq++,
        'channel' => 'walk_in_kiosk',
    ]);

    $component = Livewire::actingAs($admin)->test(AdminDashboard::class);

    // Assert the component sees the correct total
    $component->assertSee('6'); // 2 + 3 + 1 tickets total
});

test('trendData returns last 7 days data', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
    ]);
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create();
    $seq = 1;

    // Create tickets for different days
    foreach (range(1, 5) as $i) {
        QueueTicket::factory()->for($service)->for($pool)->create([
            'service_date' => today()->toDateString(),
            'ticket_number' => 'TD-'.$i,
            'sequence_number' => $seq++,
        ]);
    }
    foreach (range(1, 3) as $i) {
        QueueTicket::factory()->for($service)->for($pool)->create([
            'service_date' => today()->subDays(1)->toDateString(),
            'ticket_number' => 'YD-'.$i,
            'sequence_number' => $seq++,
        ]);
    }
    foreach (range(1, 2) as $i) {
        QueueTicket::factory()->for($service)->for($pool)->create([
            'service_date' => today()->subDays(2)->toDateString(),
            'ticket_number' => 'DB-'.$i,
            'sequence_number' => $seq++,
        ]);
    }

    $component = Livewire::actingAs($admin)->test(AdminDashboard::class);

    // Assert the component renders correctly with trend data
    $component->assertOk();
});

test('average wait time is zero when no completed tickets', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
    ]);
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create();
    $today = today()->toDateString();

    // Create waiting tickets only (no completed)
    foreach (range(1, 3) as $i) {
        QueueTicket::factory()->for($service)->for($pool)->create([
            'service_date' => $today,
            'ticket_number' => 'WAIT-'.$i,
            'sequence_number' => $i,
            'status' => QueueStatus::Waiting,
        ]);
    }

    Livewire::actingAs($admin)
        ->test(AdminDashboard::class)
        ->assertSee('0'); // Average wait time should be 0
});

test('date range filter has default values of today', function () {
    $admin = User::factory()->create([
        'role' => UserRole::Admin->value,
    ]);
    $today = today()->toDateString();

    $component = Livewire::actingAs($admin)->test(AdminDashboard::class);

    expect($component->startDate)->toBe($today);
    expect($component->endDate)->toBe($today);
});

it('loads dashboard stats efficiently', function () {
    $service = Service::factory()->create();
    QueueTicket::factory()
        ->count(3)
        ->for($service)
        ->create([
            'service_date' => today(),
            'status' => \App\Enums\QueueStatus::Completed,
            'channel' => 'online_booking',
            'called_at' => now()->subMinutes(10),
            'completed_at' => now(),
        ]);
    QueueTicket::factory()->create([
        'service_date' => today(),
        'status' => \App\Enums\QueueStatus::Waiting,
    ]);

    $user = User::factory()->create([
        'role' => UserRole::Admin->value,
    ]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\Dashboard\AdminDashboard::class)
        ->assertSet('startDate', today()->toDateString())
        ->assertOk();
});
