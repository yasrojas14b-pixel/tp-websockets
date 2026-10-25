<?php

namespace Database\Factories;

use App\Models\Addon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Addon>
 */
class AddonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Retirado', 'Nail art', 'Francesita']).' '.fake()->unique()->numberBetween(1, 9999),
            'description' => null,
            'price' => fake()->numberBetween(1, 5) * 1000,
            'duration_minutes' => fake()->randomElement([15, 20, 30]),
            'is_active' => true,
        ];
    }
}
