<?php

declare(strict_types=1);

use App\Models\Counter;
use App\Models\QueueActivity;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

describe('Activity Log', function () {
    it('returns last 20 queue activities ordered by created_at desc', function () {
        $service = Service::factory()->create();
        $user = User::factory()->create();
        $counter = Counter::factory()->create();

        // Create 25 activities
        $tickets = QueueTicket::factory()->count(25)->create([
            'service_id' => $service->id,
        ]);

        foreach ($tickets as $index => $ticket) {
            QueueActivity::factory()->create([
                'queue_ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'counter_id' => $counter->id,
                'action' => 'ticket_completed',
                'created_at' => now()->subMinutes($index),
            ]);
        }

        $component = Livewire::test(\App\Livewire\Dashboard\AdminDashboard::class);

        expect($component->recentActivities)->toHaveCount(20);
        expect($component->recentActivities->first()->created_at->greaterThan(
            $component->recentActivities->last()->created_at
        ))->toBeTrue();
    });

    it('shows empty state message when no activities exist', function () {
        Livewire::test(\App\Livewire\Dashboard\AdminDashboard::class)
            ->assertSee('Belum ada aktivitas');
    });

    it('renders activity log section with activity data', function () {
        $service = Service::factory()->create(['name' => 'Test Service']);
        $user = User::factory()->create(['name' => 'Test Officer']);
        $counter = Counter::factory()->create(['name' => 'Counter 1']);
        $ticket = QueueTicket::factory()->create([
            'service_id' => $service->id,
            'ticket_number' => 'A001',
        ]);

        QueueActivity::factory()->create([
            'queue_ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'counter_id' => $counter->id,
            'action' => 'ticket_completed',
        ]);

        Livewire::test(\App\Livewire\Dashboard\AdminDashboard::class)
            ->assertSee('Aktivitas Terkini')
            ->assertSee('A001')
            ->assertSee('Test Service')
            ->assertSee('Test Officer')
            ->assertSee('Counter 1')
            ->assertSee('Selesai');
    });

    it('returns correct action labels', function () {
        $component = Livewire::test(\App\Livewire\Dashboard\AdminDashboard::class);

        expect($component->instance()->actionLabel('ticket_called'))->toBe('Dipanggil');
        expect($component->instance()->actionLabel('ticket_completed'))->toBe('Selesai');
        expect($component->instance()->actionLabel('ticket_skipped'))->toBe('Dilewati');
        expect($component->instance()->actionLabel('ticket_cancelled'))->toBe('Dibatalkan');
        expect($component->instance()->actionLabel('ticket_recalled'))->toBe('Dipanggil Ulang');
        expect($component->instance()->actionLabel('unknown_action'))->toBe('Unknown Action');
    });

    it('returns correct action colors', function () {
        $component = Livewire::test(\App\Livewire\Dashboard\AdminDashboard::class);

        expect($component->instance()->actionColor('ticket_called'))->toBe('blue');
        expect($component->instance()->actionColor('ticket_recalled'))->toBe('blue');
        expect($component->instance()->actionColor('ticket_completed'))->toBe('green');
        expect($component->instance()->actionColor('ticket_skipped'))->toBe('red');
        expect($component->instance()->actionColor('ticket_cancelled'))->toBe('red');
        expect($component->instance()->actionColor('unknown_action'))->toBe('zinc');
    });

    it('handles null relationships gracefully', function () {
        $service = Service::factory()->create();
        $ticket = QueueTicket::factory()->create([
            'service_id' => $service->id,
        ]);

        QueueActivity::factory()->create([
            'queue_ticket_id' => $ticket->id,
            'user_id' => null,
            'counter_id' => null,
            'action' => 'ticket_completed',
        ]);

        Livewire::test(\App\Livewire\Dashboard\AdminDashboard::class)
            ->assertSee('Aktivitas Terkini')
            ->assertSee('-');
    });
});
