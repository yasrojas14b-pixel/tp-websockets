<?php

namespace Database\Factories;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = CarbonImmutable::tomorrow()->setTime(10, 0);

        return [
            'client_id' => Client::factory(),
            'service_id' => Service::factory(),
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->addMinutes(60),
            'status' => AppointmentStatus::Confirmed,
            'total_price' => 12000,
            'source' => 'admin',
        ];
    }

    /**
     * Place the appointment at a specific time with a given length.
     */
    public function at(CarbonImmutable $startsAt, int $durationMinutes = 60): static
    {
        return $this->state(fn (array $attributes) => [
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->addMinutes($durationMinutes),
        ]);
    }

    public function status(AppointmentStatus $status): static
    {
        return $this->state(fn (array $attributes) => ['status' => $status]);
    }
}
