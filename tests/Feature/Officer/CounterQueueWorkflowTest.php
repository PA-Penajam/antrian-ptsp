<?php

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
        'status' => 'waiting',
        'service_date' => '2026-03-10',
        'sequence_number' => 1,
        'ticket_number' => 'UMUM-0001',
    ]);
    $secondUmumTicket = QueueTicket::factory()->for($umumService)->for($umumPool)->create([
        'status' => 'waiting',
        'service_date' => '2026-03-10',
        'sequence_number' => 2,
        'ticket_number' => 'UMUM-0002',
    ]);
    $bayarTicket = QueueTicket::factory()->for($bayarService)->for($bayarPool)->create([
        'status' => 'waiting',
        'service_date' => '2026-03-10',
        'sequence_number' => 1,
        'ticket_number' => 'BAYAR-0001',
    ]);

    $callNext = $this->actingAs($officer)->post("/petugas/loket/{$umumCounter->id}/call-next");
    $callNext->assertOk()->assertSee('Panggil Berikutnya');

    expect($firstUmumTicket->fresh()->status)->toBe('called')
        ->and($firstUmumTicket->fresh()->counter_id)->toBe($umumCounter->id)
        ->and($bayarTicket->fresh()->status)->toBe('waiting');

    $recall = $this->actingAs($officer)->post("/petugas/loket/{$umumCounter->id}/recall", [
        'ticket_id' => $firstUmumTicket->id,
    ]);
    $recall->assertOk()->assertSee('Panggil Ulang');

    $complete = $this->actingAs($officer)->post("/petugas/loket/{$umumCounter->id}/complete", [
        'ticket_id' => $firstUmumTicket->id,
    ]);
    $complete->assertOk()->assertSee('Selesai');

    expect($firstUmumTicket->fresh()->status)->toBe('completed')
        ->and($firstUmumTicket->fresh()->completed_at)->not->toBeNull();

    $callNextAgain = $this->actingAs($officer)->post("/petugas/loket/{$umumCounter->id}/call-next");
    $callNextAgain->assertOk();

    expect($secondUmumTicket->fresh()->status)->toBe('called')
        ->and($secondUmumTicket->fresh()->counter_id)->toBe($umumCounter->id);

    $skip = $this->actingAs($officer)->post("/petugas/loket/{$umumCounter->id}/skip", [
        'ticket_id' => $secondUmumTicket->id,
    ]);
    $skip->assertOk()->assertSee('Lewati');

    expect($secondUmumTicket->fresh()->status)->toBe('skipped');

    $callBayarFromBayarCounter = $this->actingAs($officer)->post("/petugas/loket/{$bayarCounter->id}/call-next");
    $callBayarFromBayarCounter->assertOk();

    expect($bayarTicket->fresh()->status)->toBe('called')
        ->and($bayarTicket->fresh()->counter_id)->toBe($bayarCounter->id);
});
