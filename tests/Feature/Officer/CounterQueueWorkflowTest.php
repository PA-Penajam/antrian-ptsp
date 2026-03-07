<?php

use App\Enums\QueueStatus;
use App\Enums\UserRole;
use App\Models\Counter;
use App\Models\QueuePool;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\User;

test('officer can run queue workflow and only serves own pool', function () {
    $officer = User::factory()->create([
        'role' => UserRole::Officer->value,
        'email_verified_at' => now(),
    ]);

    $umumPool = QueuePool::factory()->create(['code' => 'UMUM']);
    $bayarPool = QueuePool::factory()->create(['code' => 'BAYAR']);
    $umumService = Service::factory()->for($umumPool)->create();
    $bayarService = Service::factory()->for($bayarPool)->create();
    $umumCounter = Counter::factory()->for($umumPool)->create(['code' => 'U1']);
    $bayarCounter = Counter::factory()->for($bayarPool)->create(['code' => 'BYR']);

    $firstUmumTicket = QueueTicket::factory()->for($umumService)->for($umumPool)->create([
        'status' => QueueStatus::Waiting,
        'counter_id' => null,
        'service_date' => '2026-03-10',
        'sequence_number' => 1,
        'ticket_number' => 'UMUM-0001',
    ]);
    $secondUmumTicket = QueueTicket::factory()->for($umumService)->for($umumPool)->create([
        'status' => QueueStatus::Waiting,
        'counter_id' => null,
        'service_date' => '2026-03-10',
        'sequence_number' => 2,
        'ticket_number' => 'UMUM-0002',
    ]);
    $bayarTicket = QueueTicket::factory()->for($bayarService)->for($bayarPool)->create([
        'status' => QueueStatus::Waiting,
        'counter_id' => null,
        'service_date' => '2026-03-10',
        'sequence_number' => 1,
        'ticket_number' => 'BAYAR-0001',
    ]);
    $officer->services()->attach($umumService);

    $callNext = $this->actingAs($officer)->post("/petugas/loket/{$umumCounter->id}/call-next");
    $callNext->assertOk()->assertSee('Panggil Berikutnya');

    expect($firstUmumTicket->fresh()->status)->toBe(QueueStatus::Called)
        ->and($firstUmumTicket->fresh()->counter_id)->toBe($umumCounter->id)
        ->and($bayarTicket->fresh()->status)->toBe(QueueStatus::Waiting);

    $recall = $this->actingAs($officer)->post("/petugas/loket/{$umumCounter->id}/recall", [
        'ticket_id' => $firstUmumTicket->id,
    ]);
    $recall->assertOk()->assertSee('Panggil Ulang');

    $complete = $this->actingAs($officer)->post("/petugas/loket/{$umumCounter->id}/complete", [
        'ticket_id' => $firstUmumTicket->id,
    ]);
    $complete->assertOk()->assertSee('Selesai');

    expect($firstUmumTicket->fresh()->status)->toBe(QueueStatus::Completed)
        ->and($firstUmumTicket->fresh()->completed_at)->not->toBeNull();

    $callNextAgain = $this->actingAs($officer)->post("/petugas/loket/{$umumCounter->id}/call-next");
    $callNextAgain->assertOk();

    expect($secondUmumTicket->fresh()->status)->toBe(QueueStatus::Called)
        ->and($secondUmumTicket->fresh()->counter_id)->toBe($umumCounter->id);

    $skip = $this->actingAs($officer)->post("/petugas/loket/{$umumCounter->id}/skip", [
        'ticket_id' => $secondUmumTicket->id,
    ]);
    $skip->assertOk()->assertSee('Lewati');

    expect($secondUmumTicket->fresh()->status)->toBe(QueueStatus::Skipped);

    $callBayarFromBayarCounter = $this->actingAs($officer)->post("/petugas/loket/{$bayarCounter->id}/call-next");
    $callBayarFromBayarCounter->assertOk()->assertSee('Tidak ada antrean');

    expect($bayarTicket->fresh()->status)->toBe(QueueStatus::Waiting)
        ->and($bayarTicket->fresh()->counter_id)->toBeNull();
});

test('officer only claims eligible oldest ticket and claimed ticket is not reused', function () {
    $officerA = User::factory()->create([
        'role' => UserRole::Officer->value,
        'email_verified_at' => now(),
    ]);
    $officerB = User::factory()->create([
        'role' => UserRole::Officer->value,
        'email_verified_at' => now(),
    ]);

    $pool = QueuePool::factory()->create(['code' => 'UMUM']);
    $allowedService = Service::factory()->for($pool)->create();
    $blockedService = Service::factory()->for($pool)->create();
    $counter = Counter::factory()->for($pool)->create(['code' => 'U2']);

    $olderBlocked = QueueTicket::factory()->for($blockedService)->for($pool)->create([
        'status' => QueueStatus::Waiting,
        'counter_id' => null,
        'service_date' => '2026-03-10',
        'sequence_number' => 1,
        'ticket_number' => 'UMUM-0001',
    ]);
    $olderAllowed = QueueTicket::factory()->for($allowedService)->for($pool)->create([
        'status' => QueueStatus::Waiting,
        'counter_id' => null,
        'service_date' => '2026-03-10',
        'sequence_number' => 2,
        'ticket_number' => 'UMUM-0002',
    ]);
    $nextAllowed = QueueTicket::factory()->for($allowedService)->for($pool)->create([
        'status' => QueueStatus::Waiting,
        'counter_id' => null,
        'service_date' => '2026-03-10',
        'sequence_number' => 3,
        'ticket_number' => 'UMUM-0003',
    ]);

    $officerA->services()->attach($allowedService);
    $officerB->services()->attach($allowedService);

    $firstClaim = $this->actingAs($officerA)->post("/petugas/loket/{$counter->id}/call-next");
    $firstClaim->assertOk()->assertSee('UMUM-0002');

    $secondClaim = $this->actingAs($officerB)->post("/petugas/loket/{$counter->id}/call-next");
    $secondClaim->assertOk()->assertSee('UMUM-0003');

    expect($olderBlocked->fresh()->status)->toBe(QueueStatus::Waiting)
        ->and($olderAllowed->fresh()->status)->toBe(QueueStatus::Called)
        ->and($nextAllowed->fresh()->status)->toBe(QueueStatus::Called)
        ->and($olderAllowed->fresh()->id)->not->toBe($nextAllowed->fresh()->id);
});

test('officer dashboard entry shows workstation context', function () {
    $officer = User::factory()->create([
        'role' => UserRole::Officer->value,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($officer)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Modul Panggilan Petugas')
        ->assertSee('Panggil Berikutnya')
        ->assertSee('Daftar Skip Layanan');
});
