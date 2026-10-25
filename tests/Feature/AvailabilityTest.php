<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Models\Addon;
use App\Models\Appointment;
use App\Models\BusinessHour;
use App\Models\ScheduleBlock;
use App\Models\Service;
use App\Services\AvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private CarbonImmutable $tuesday;

    protected function setUp(): void
    {
        parent::setUp();

        // Monday 2026-10-19 08:00; we book on Tuesday 2026-10-20.
        CarbonImmutable::setTestNow('2026-10-19 08:00:00');
        $this->tuesday = CarbonImmutable::parse('2026-10-20');

        config(['salon.slot_interval_minutes' => 30, 'salon.min_notice_minutes' => 60]);

        BusinessHour::factory()->create(['weekday' => 2, 'opens_at' => '09:00', 'closes_at' => '13:00']);
        BusinessHour::factory()->create(['weekday' => 2, 'opens_at' => '14:00', 'closes_at' => '16:00']);
    }

    public function test_slots_fit_inside_business_hours_and_skip_the_lunch_break(): void
    {
        $slots = app(AvailabilityService::class)->slotsFor($this->tuesday, 90);

        $this->assertSame(['09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '14:00', '14:30'], $slots->all());
    }

    public function test_slots_overlapping_an_active_appointment_are_removed(): void
    {
        Appointment::factory()->at($this->tuesday->setTime(10, 0), 60)->create();

        $slots = app(AvailabilityService::class)->slotsFor($this->tuesday, 60);

        $this->assertNotContains('09:30', $slots);
        $this->assertNotContains('10:00', $slots);
        $this->assertNotContains('10:30', $slots);
        $this->assertContains('09:00', $slots, 'A slot ending exactly when another starts must be offered.');
        $this->assertContains('11:00', $slots, 'A slot starting exactly when another ends must be offered.');
    }

    public function test_cancelled_appointments_free_their_slot(): void
    {
        Appointment::factory()->at($this->tuesday->setTime(10, 0))->status(AppointmentStatus::Cancelled)->create();

        $this->assertContains('10:00', app(AvailabilityService::class)->slotsFor($this->tuesday, 60));
    }

    public function test_schedule_blocks_remove_slots(): void
    {
        ScheduleBlock::factory()->create([
            'starts_at' => $this->tuesday->setTime(9, 0),
            'ends_at' => $this->tuesday->setTime(13, 0),
        ]);

        $this->assertSame(['14:00', '14:30', '15:00'], app(AvailabilityService::class)->slotsFor($this->tuesday, 60)->all());
    }

    public function test_closed_days_have_no_slots(): void
    {
        $sunday = CarbonImmutable::parse('2026-10-25');

        $this->assertTrue(app(AvailabilityService::class)->slotsFor($sunday, 60)->isEmpty());
    }

    public function test_slots_respect_minimum_notice(): void
    {
        CarbonImmutable::setTestNow('2026-10-20 09:10:00');

        $slots = app(AvailabilityService::class)->slotsFor($this->tuesday, 60);

        $this->assertSame('10:30', $slots->first());
    }

    public function test_availability_endpoint_adds_addon_duration(): void
    {
        $service = Service::factory()->create(['duration_minutes' => 90]);
        $addon = Addon::factory()->create(['duration_minutes' => 30]);
        $service->addons()->attach($addon);

        $this->getJson(route('availability', ['service_id' => $service->id, 'addons' => [$addon->id], 'date' => '2026-10-20']))
            ->assertOk()
            ->assertJsonPath('duration_minutes', 120)
            ->assertJsonPath('slots', ['09:00', '09:30', '10:00', '10:30', '11:00', '14:00']);
    }
}
