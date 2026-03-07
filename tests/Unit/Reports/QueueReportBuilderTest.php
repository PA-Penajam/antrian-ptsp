<?php

use App\Models\Counter;
use App\Models\QueueActivity;
use App\Models\QueuePool;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\User;
use App\Support\Reports\QueueReportBuilder;

test('report builder aggregates by service counter officer and status', function () {
    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $serviceA = Service::factory()->for($pool)->create(['name' => 'Pendaftaran']);
    $serviceB = Service::factory()->for($pool)->create(['name' => 'Informasi/Pengaduan']);
    $counter1 = Counter::factory()->for($pool)->create(['name' => 'Loket Umum 1']);
    $counter2 = Counter::factory()->for($pool)->create(['name' => 'Loket Umum 2']);
    $officer1 = User::factory()->create(['name' => 'Officer Satu']);
    $officer2 = User::factory()->create(['name' => 'Officer Dua']);

    $ticket1 = QueueTicket::factory()->for($serviceA)->for($pool)->for($counter1)->for($officer1, 'creator')->create([
        'service_date' => '2026-03-10',
        'status' => 'completed',
    ]);
    $ticket2 = QueueTicket::factory()->for($serviceA)->for($pool)->for($counter1)->for($officer1, 'creator')->create([
        'service_date' => '2026-03-10',
        'status' => 'waiting',
    ]);
    $ticket3 = QueueTicket::factory()->for($serviceB)->for($pool)->for($counter2)->for($officer2, 'creator')->create([
        'service_date' => '2026-03-10',
        'status' => 'cancelled',
    ]);
    QueueTicket::factory()->for($serviceB)->for($pool)->for($counter2)->for($officer2, 'creator')->create([
        'service_date' => '2026-03-11',
        'status' => 'completed',
    ]);

    QueueActivity::factory()->for($ticket1)->for($officer1)->for($counter1)->create([
        'action' => 'ticket_completed',
    ]);
    QueueActivity::factory()->for($ticket3)->for($officer2)->for($counter2)->create([
        'action' => 'ticket_completed',
    ]);
    QueueActivity::factory()->for($ticket2)->for($officer1)->for($counter1)->create([
        'action' => 'ticket_recalled',
    ]);

    $report = app(QueueReportBuilder::class)->build('2026-03-10', '2026-03-10');

    expect($report['by_service']['Pendaftaran'])->toBe(2)
        ->and($report['by_service']['Informasi/Pengaduan'])->toBe(1)
        ->and($report['by_counter']['Loket Umum 1'])->toBe(2)
        ->and($report['by_counter']['Loket Umum 2'])->toBe(1)
        ->and($report['by_officer']['Officer Satu'])->toBe(2)
        ->and($report['by_officer']['Officer Dua'])->toBe(1)
        ->and($report['by_status']['completed'])->toBe(1)
        ->and($report['by_status']['waiting'])->toBe(1)
        ->and($report['by_status']['cancelled'])->toBe(1)
        ->and($report['officer_service_distribution']['Officer Satu']['Pendaftaran'])->toBe(1)
        ->and($report['officer_service_distribution']['Officer Dua']['Informasi/Pengaduan'])->toBe(1);
});
