<?php

namespace Tests\Unit\Models;

use App\Enums\QueueStatus;
use App\Models\QueuePool;
use App\Models\QueueTicket;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_remaining_quota_returns_null_when_no_daily_quota(): void
    {
        $service = Service::factory()->create(['daily_quota' => null]);

        $this->assertNull($service->getRemainingQuota());
    }

    public function test_get_remaining_quota_returns_full_quota_when_no_tickets(): void
    {
        $service = Service::factory()->create(['daily_quota' => 10]);

        $this->assertEquals(10, $service->getRemainingQuota());
    }

    public function test_get_remaining_quota_subtracts_active_tickets(): void
    {
        $pool = QueuePool::factory()->create();
        $service = Service::factory()->create(['daily_quota' => 10, 'queue_pool_id' => $pool->id]);

        // 3 tiket aktif hari ini
        QueueTicket::factory()->count(3)->create([
            'service_id' => $service->id,
            'queue_pool_id' => $pool->id,
            'service_date' => today(),
            'status' => QueueStatus::Waiting,
        ]);

        $this->assertEquals(7, $service->getRemainingQuota());
    }

    public function test_get_remaining_quota_ignores_cancelled_tickets(): void
    {
        $pool = QueuePool::factory()->create();
        $service = Service::factory()->create(['daily_quota' => 10, 'queue_pool_id' => $pool->id]);

        QueueTicket::factory()->count(2)->create([
            'service_id' => $service->id,
            'queue_pool_id' => $pool->id,
            'service_date' => today(),
            'status' => QueueStatus::Waiting,
        ]);
        // Cancelled tidak dihitung
        QueueTicket::factory()->create([
            'service_id' => $service->id,
            'queue_pool_id' => $pool->id,
            'service_date' => today(),
            'status' => QueueStatus::Cancelled,
        ]);

        $this->assertEquals(8, $service->getRemainingQuota());
    }

    public function test_get_remaining_quota_respects_specific_date(): void
    {
        $pool = QueuePool::factory()->create();
        $service = Service::factory()->create(['daily_quota' => 10, 'queue_pool_id' => $pool->id]);

        // Tiket untuk besok
        QueueTicket::factory()->count(5)->create([
            'service_id' => $service->id,
            'queue_pool_id' => $pool->id,
            'service_date' => today()->addDay(),
            'status' => QueueStatus::Waiting,
        ]);

        // Hari ini harus tetap 10
        $this->assertEquals(10, $service->getRemainingQuota(today()->toDateString()));
        // Besok harus 5
        $this->assertEquals(5, $service->getRemainingQuota(today()->addDay()->toDateString()));
    }

    public function test_get_remaining_quota_never_below_zero(): void
    {
        $pool = QueuePool::factory()->create();
        $service = Service::factory()->create(['daily_quota' => 2, 'queue_pool_id' => $pool->id]);

        QueueTicket::factory()->count(5)->create([
            'service_id' => $service->id,
            'queue_pool_id' => $pool->id,
            'service_date' => today(),
            'status' => QueueStatus::Waiting,
        ]);

        $this->assertEquals(0, $service->getRemainingQuota());
    }
}
