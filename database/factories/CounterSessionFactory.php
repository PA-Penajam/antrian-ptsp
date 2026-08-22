<?php

namespace Database\Factories;

use App\Models\Counter;
use App\Models\CounterSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CounterSession>
 */
class CounterSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'counter_id' => Counter::factory(),
            'user_id' => User::factory(),
            'opened_at' => now(),
            'closed_at' => null,
            'status' => 'open',
        ];
    }
}
