<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->firstNameFemale().' '.fake()->lastName(),
            'phone' => '54911'.fake()->unique()->numerify('########'),
            'email' => fake()->optional()->safeEmail(),
            'nail_type' => fake()->optional()->randomElement(['Finas y quebradizas', 'Fuertes', 'Mordidas', 'Cutícula seca']),
            'allergies' => null,
            'preferred_designs' => fake()->optional()->randomElement(['Tonos nude', 'Francesita', 'Glitter', 'Diseños florales']),
            'internal_notes' => null,
        ];
    }
}
