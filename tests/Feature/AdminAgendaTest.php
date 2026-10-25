<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\BusinessHour;
use App\Models\Client;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAgendaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private CarbonImmutable $tuesday;

    protected function setUp(): void
    {
        parent::setUp();

        CarbonImmutable::setTestNow('2026-10-19 08:00:00');
        $this->tuesday = CarbonImmutable::parse('2026-10-20');
        $this->admin = User::factory()->create();
        BusinessHour::factory()->create(['weekday' => 2, 'opens_at' => '09:00', 'closes_at' => '19:00']);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('admin.calendar'))->assertRedirect(route('login'));
    }

    public function test_every_admin_page_renders(): void
    {
        $appointment = Appointment::factory()->at($this->tuesday->setTime(10, 0))->create();

        $this->actingAs($this->admin);

        foreach ([
            route('admin.dashboard'),
            route('admin.calendar'),
            route('admin.appointments.index'),
            route('admin.appointments.create'),
            route('admin.appointments.edit', $appointment),
            route('admin.clients.index'),
            route('admin.clients.show', $appointment->client),
            route('admin.clients.create'),
            route('admin.services.index'),
            route('admin.services.create'),
            route('admin.addons.create'),
            route('admin.blocks.index'),
            route('admin.hours.edit'),
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_calendar_feed_returns_appointments_in_range(): void
    {
        $appointment = Appointment::factory()->at($this->tuesday->setTime(10, 0))->create();

        $this->actingAs($this->admin)
            ->getJson(route('admin.calendar.events', ['start' => '2026-10-19T00:00:00-03:00', 'end' => '2026-10-26T00:00:00-03:00']))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', 'appointment-'.$appointment->id);
    }

    public function test_admin_can_book_outside_hours_but_never_overlapping(): void
    {
        $client = Client::factory()->create();
        $service = Service::factory()->create(['duration_minutes' => 60]);
        $data = [
            'client_id' => $client->id,
            'service_id' => $service->id,
            'date' => '2026-10-20',
            'time' => '20:00',
            'status' => AppointmentStatus::Confirmed->value,
            'ignore_business_hours' => '1',
        ];

        $this->actingAs($this->admin)->post(route('admin.appointments.store'), $data)->assertSessionHasNoErrors();
        $this->post(route('admin.appointments.store'), [...$data, 'time' => '20:30'])->assertSessionHasErrors('time');

        $this->assertSame(1, Appointment::count());
    }

    public function test_drag_and_drop_onto_a_taken_slot_returns_conflict(): void
    {
        Appointment::factory()->at($this->tuesday->setTime(10, 0))->create();
        $moving = Appointment::factory()->at($this->tuesday->setTime(14, 0))->create();

        $this->actingAs($this->admin)
            ->patchJson(route('admin.appointments.schedule', $moving), [
                'start' => '2026-10-20T10:30:00-03:00',
                'end' => '2026-10-20T11:30:00-03:00',
            ])
            ->assertStatus(409);

        $this->assertSame('14:00', $moving->fresh()->starts_at->format('H:i'));
    }

    public function test_drag_and_drop_onto_a_free_slot_moves_the_appointment(): void
    {
        $moving = Appointment::factory()->at($this->tuesday->setTime(14, 0))->create();

        $this->actingAs($this->admin)
            ->patchJson(route('admin.appointments.schedule', $moving), [
                'start' => '2026-10-20T16:00:00-03:00',
                'end' => '2026-10-20T17:30:00-03:00',
            ])
            ->assertOk();

        $moving->refresh();
        $this->assertSame('16:00', $moving->starts_at->format('H:i'));
        $this->assertSame('17:30', $moving->ends_at->format('H:i'));
    }

    public function test_cancelled_appointment_cannot_be_reactivated_if_its_slot_was_taken(): void
    {
        $cancelled = Appointment::factory()->at($this->tuesday->setTime(10, 0))->status(AppointmentStatus::Cancelled)->create();
        Appointment::factory()->at($this->tuesday->setTime(10, 0))->create();

        $this->actingAs($this->admin)
            ->patch(route('admin.appointments.status', $cancelled), ['status' => AppointmentStatus::Confirmed->value])
            ->assertSessionHasErrors('status');

        $this->assertSame(AppointmentStatus::Cancelled, $cancelled->fresh()->status);
    }
}
