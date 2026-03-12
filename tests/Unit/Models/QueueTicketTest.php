<?php

namespace Tests\Unit\Models;

use App\Enums\QueueStatus;
use App\Models\QueuePool;
use App\Models\QueueTicket;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueTicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_queue_position_returns_null_for_non_waiting_status(): void
    {
        $ticket = QueueTicket::factory()->create(['status' => QueueStatus::Completed]);

        $this->assertNull($ticket->getQueuePosition());
    }

    public function test_get_queue_position_returns_1_for_first_waiting_ticket(): void
    {
        $pool = QueuePool::factory()->create();
        $service = Service::factory()->create(['queue_pool_id' => $pool->id]);
        $ticket = QueueTicket::factory()->create([
            'service_id' => $service->id,
            'queue_pool_id' => $pool->id,
            'service_date' => today(),
            'status' => QueueStatus::Waiting,
            'sequence_number' => 1,
        ]);

        $this->assertEquals(1, $ticket->getQueuePosition());
    }

    public function test_get_queue_position_returns_correct_position_with_multiple_tickets(): void
    {
        $pool = QueuePool::factory()->create();
        $service = Service::factory()->create(['queue_pool_id' => $pool->id]);

        // Buat 3 tiket waiting
        QueueTicket::factory()->create([
            'service_id' => $service->id,
            'queue_pool_id' => $pool->id,
            'service_date' => today(),
            'status' => QueueStatus::Waiting,
            'sequence_number' => 1,
        ]);
        QueueTicket::factory()->create([
            'service_id' => $service->id,
            'queue_pool_id' => $pool->id,
            'service_date' => today(),
            'status' => QueueStatus::Waiting,
            'sequence_number' => 2,
        ]);
        $thirdTicket = QueueTicket::factory()->create([
            'service_id' => $service->id,
            'queue_pool_id' => $pool->id,
            'service_date' => today(),
            'status' => QueueStatus::Waiting,
            'sequence_number' => 3,
        ]);

        $this->assertEquals(3, $thirdTicket->getQueuePosition());
    }

    public function test_get_queue_position_ignores_non_waiting_tickets(): void
    {
        $pool = QueuePool::factory()->create();
        $service = Service::factory()->create(['queue_pool_id' => $pool->id]);

        // Tiket completed (harus diabaikan)
        QueueTicket::factory()->create([
            'service_id' => $service->id,
            'queue_pool_id' => $pool->id,
            'service_date' => today(),
            'status' => QueueStatus::Completed,
            'sequence_number' => 1,
        ]);
        // Tiket waiting
        $waitingTicket = QueueTicket::factory()->create([
            'service_id' => $service->id,
            'queue_pool_id' => $pool->id,
            'service_date' => today(),
            'status' => QueueStatus::Waiting,
            'sequence_number' => 2,
        ]);

        // Posisi harus 1, bukan 2 (karena completed diabaikan)
        $this->assertEquals(1, $waitingTicket->getQueuePosition());
    }
}
