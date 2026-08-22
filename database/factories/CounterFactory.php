<?php

namespace Database\Factories;

use App\Models\Counter;
use App\Models\QueuePool;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Counter>
 */
class CounterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'queue_pool_id' => QueuePool::factory(),
            'name' => 'Loket '.fake()->numberBetween(1, 20),
            'code' => strtoupper(fake()->unique()->bothify('LKT##')),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }
}
