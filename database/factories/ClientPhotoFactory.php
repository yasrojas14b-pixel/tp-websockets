<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\ClientPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClientPhoto>
 */
class ClientPhotoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'path' => 'clients/example/'.fake()->uuid().'.jpg',
            'caption' => fake()->optional()->sentence(3),
        ];
    }
}
