<?php

namespace Database\Factories;

use App\Models\Counter;
use App\Models\QueueTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QueueActivity>
 */
class QueueActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'queue_ticket_id' => QueueTicket::factory(),
            'user_id' => User::factory(),
            'counter_id' => Counter::factory(),
            'action' => 'created',
            'meta' => [
                'source' => 'factory',
            ],
        ];
    }
}
