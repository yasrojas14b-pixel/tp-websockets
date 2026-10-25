<?php

namespace Database\Factories;

use App\Models\BusinessHour;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusinessHour>
 */
class BusinessHourFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'weekday' => fake()->numberBetween(1, 5),
            'opens_at' => '09:00',
            'closes_at' => '18:00',
        ];
    }
}
