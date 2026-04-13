<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QueuePool>
 */
class QueuePoolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => strtoupper(fake()->lexify('Pool ???')),
            'code' => strtoupper(fake()->unique()->bothify('P??##')),
            'letter_code' => strtoupper(fake()->lexify('?')),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
