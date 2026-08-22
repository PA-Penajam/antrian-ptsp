<?php

namespace Database\Factories;

use App\Models\QueuePool;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'queue_pool_id' => QueuePool::factory(),
            'name' => Str::title($name),
            'code' => strtoupper(fake()->unique()->bothify('S??##')),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(10, 99),
            'description' => fake()->sentence(),
            'requirements' => fake()->sentence(),
            'is_active' => true,
            'booking_enabled' => true,
            'walk_in_enabled' => true,
            'daily_quota' => fake()->numberBetween(10, 150),
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}
