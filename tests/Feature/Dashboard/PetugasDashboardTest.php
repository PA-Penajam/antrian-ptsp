<?php

declare(strict_types=1);

use App\Enums\QueueStatus;
use App\Enums\UserRole;
use App\Models\Counter;
use App\Models\QueuePool;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

describe('PetugasDashboard', function () {
    it('renders the workstation with counter selection and action buttons', function () {
        $officer = User::factory()->create(['role' => UserRole::Officer->value]);
        $pool = QueuePool::factory()->create(['code' => 'TEST']);
        $service = Service::factory()->for($pool)->create(['name' => 'Layanan A']);
        $counter = Counter::factory()->for($pool)->create(['name' => 'Loket 1']);
        $officer->services()->attach($service);

        Livewire::actingAs($officer)
            ->test('dashboard.petugas-dashboard')
            ->assertOk()
            ->assertSee('Loket 1')
            ->assertSee('Panggil Berikutnya')
            ->assertSee('Panggil Ulang')
            ->assertSee('Lewati')
            ->assertSee('Selesai');
    });

    it('resolveSelectedCounter uses counters array instead of re-querying services', function () {
        $officer = User::factory()->create(['role' => UserRole::Officer->value]);
        $pool = QueuePool::factory()->create(['code' => 'CACHE']);
        $service = Service::factory()->for($pool)->create();
        $counter = Counter::factory()->for($pool)->create(['name' => 'Loket Cache']);
        $officer->services()->attach($service);

        QueueTicket::factory()->for($service)->for($pool)->create([
            'status' => QueueStatus::Waiting,
            'service_date' => now()->toDateString(),
            'sequence_number' => 1,
            'ticket_number' => 'CACHE-001',
        ]);

        $component = Livewire::actingAs($officer)
            ->test('dashboard.petugas-dashboard');

        // counters array is populated from syncBoard — counter should be present
        expect($component->counters)
            ->not->toBeEmpty()
            ->and(collect($component->counters)->pluck('id'))->toContain($counter->id);

        // Calling callNext should succeed using cached counters, not re-querying services
        $component->call('callNext');

        expect($component->feedbackTone)->not->toBe('amber', 'Expected callNext to succeed using cached counter resolution');
    });

    it('resolveSelectedCounter returns null when selectedCounterId is not in cached counters', function () {
        $officer = User::factory()->create(['role' => UserRole::Officer->value]);
        $pool = QueuePool::factory()->create(['code' => 'AUTH']);
        $service = Service::factory()->for($pool)->create();
        $counter = Counter::factory()->for($pool)->create();
        $officer->services()->attach($service);

        // A counter from a different pool the officer does not have access to
        $otherPool = QueuePool::factory()->create(['code' => 'OTHER']);
        $otherCounter = Counter::factory()->for($otherPool)->create(['name' => 'Loket Lain']);

        $component = Livewire::actingAs($officer)
            ->test('dashboard.petugas-dashboard', ['counterId' => $otherCounter->id]);

        // Trying to call next should fail since officer can't access other pool counter
        $component->call('callNext');

        expect($component->feedbackTone)->toBe('amber');
    });

    it('stats caching skips rebuild when date has not changed', function () {
        $officer = User::factory()->create(['role' => UserRole::Officer->value]);
        $pool = QueuePool::factory()->create(['code' => 'STAT']);
        $service = Service::factory()->for($pool)->create();
        $counter = Counter::factory()->for($pool)->create();
        $officer->services()->attach($service);

        $component = Livewire::actingAs($officer)
            ->test('dashboard.petugas-dashboard');

        $initialStats = $component->stats;

        // refreshBoard should reuse cached stats for the same date
        $component->call('refreshBoard');

        expect($component->stats)->toBe($initialStats);
    });

    it('shows feedback when officer has no assigned services', function () {
        $officer = User::factory()->create(['role' => UserRole::Officer->value]);

        Livewire::actingAs($officer)
            ->test('dashboard.petugas-dashboard')
            ->assertSee('Akun petugas belum memiliki layanan yang diizinkan.');
    });
});
