<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        return [
            'name' => fake()->randomElement(['Kapping', 'Semipermanente', 'Esculpidas', 'Manicuría']).' '.fake()->unique()->numberBetween(1, 9999),
            'description' => fake()->sentence(),
            'price' => fake()->numberBetween(8, 25) * 1000,
            'duration_minutes' => fake()->randomElement([45, 60, 90, 120]),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}
