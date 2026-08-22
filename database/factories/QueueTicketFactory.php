<?php

namespace Database\Factories;

use App\Enums\QueueStatus;
use App\Models\Counter;
use App\Models\QueuePool;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QueueTicket>
 */
class QueueTicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_id' => Service::factory(),
            'queue_pool_id' => QueuePool::factory(),
            'counter_id' => Counter::factory(),
            'created_by' => User::factory(),
            'channel' => 'assisted_same_day',
            'ticket_number' => fake()->randomElement(['A', 'B', 'C', 'D', 'E']).str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'sequence_number' => fake()->unique()->numberBetween(1, 999999),
            'service_date' => fake()->date(),
            'visitor_name' => fake()->name(),
            'visitor_identifier' => (string) fake()->numerify('################'),
            'visitor_phone' => fake()->phoneNumber(),
            'visitor_wilayah_kode' => null,
            'notes' => fake()->sentence(),
            'status' => QueueStatus::Waiting,
            'checked_in_at' => null,
            'called_at' => null,
            'started_at' => null,
            'completed_at' => null,
            'cancelled_at' => null,
        ];
    }
}
