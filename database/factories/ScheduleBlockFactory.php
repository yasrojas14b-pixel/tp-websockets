<?php

namespace Database\Factories;

use App\Models\ScheduleBlock;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScheduleBlock>
 */
class ScheduleBlockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = CarbonImmutable::tomorrow()->setTime(13, 0);

        return [
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->addHour(),
            'reason' => 'Descanso',
        ];
    }
}
