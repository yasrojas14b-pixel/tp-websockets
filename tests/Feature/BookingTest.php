<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Events\AgendaUpdated;
use App\Exceptions\SlotUnavailableException;
use App\Models\Addon;
use App\Models\Appointment;
use App\Models\BusinessHour;
use App\Models\Client;
use App\Models\Service;
use App\Services\AppointmentScheduler;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        CarbonImmutable::setTestNow('2026-10-19 08:00:00');
        BusinessHour::factory()->create(['weekday' => 2, 'opens_at' => '09:00', 'closes_at' => '19:00']);
        $this->service = Service::factory()->create(['duration_minutes' => 90, 'price' => 16000]);
    }

    public function test_booking_page_lists_active_services(): void
    {
        $inactive = Service::factory()->inactive()->create();

        $this->get(route('booking.create'))
            ->assertOk()
            ->assertSee($this->service->name)
            ->assertDontSee($inactive->name);
    }

    public function test_client_can_book_a_free_slot(): void
    {
        Event::fake([AgendaUpdated::class]);
        $addon = Addon::factory()->create(['price' => 3000, 'duration_minutes' => 20]);
        $this->service->addons()->attach($addon);

        $response = $this->post(route('booking.store'), $this->bookingData(['addons' => [$addon->id]]));

        $appointment = Appointment::sole();
        $response->assertRedirect(route('booking.show', $appointment));
        $this->assertSame(AppointmentStatus::Pending, $appointment->status);
        $this->assertSame('2026-10-20 10:00', $appointment->starts_at->format('Y-m-d H:i'));
        $this->assertSame('2026-10-20 11:50', $appointment->ends_at->format('Y-m-d H:i'));
        $this->assertSame('19000.00', $appointment->total_price);
        $this->assertSame('5491123456789', $appointment->client->phone);
        Event::assertDispatched(AgendaUpdated::class, fn (AgendaUpdated $event) => $event->dates === ['2026-10-20']);
    }

    public function test_returning_client_is_matched_by_phone(): void
    {
        $client = Client::factory()->create(['phone' => '5491123456789']);

        $this->post(route('booking.store'), $this->bookingData(['phone' => '+54 9 11 2345-6789']));

        $this->assertSame(1, Client::count());
        $this->assertTrue(Appointment::sole()->client->is($client));
    }

    public function test_the_same_slot_cannot_be_booked_twice(): void
    {
        $this->post(route('booking.store'), $this->bookingData());

        $this->post(route('booking.store'), $this->bookingData(['phone' => '1155554444', 'name' => 'Otra Clienta']))
            ->assertSessionHasErrors('time');

        $this->assertSame(1, Appointment::count());
    }

    public function test_partially_overlapping_booking_is_rejected(): void
    {
        $this->post(route('booking.store'), $this->bookingData(['time' => '10:00']));

        // 10:00-11:30 is taken; 11:00-12:30 overlaps its last half hour.
        $this->post(route('booking.store'), $this->bookingData(['time' => '11:00', 'phone' => '1155554444']))
            ->assertSessionHasErrors('time');

        $this->assertSame(1, Appointment::count());
    }

    public function test_back_to_back_bookings_are_allowed(): void
    {
        $this->post(route('booking.store'), $this->bookingData(['time' => '10:00']));
        $this->post(route('booking.store'), $this->bookingData(['time' => '11:30', 'phone' => '1155554444']))
            ->assertSessionHasNoErrors();

        $this->assertSame(2, Appointment::count());
    }

    public function test_booking_outside_business_hours_is_rejected(): void
    {
        $this->post(route('booking.store'), $this->bookingData(['time' => '18:00']))
            ->assertSessionHasErrors('time');

        $this->assertSame(0, Appointment::count());
    }

    public function test_addon_from_another_service_is_rejected(): void
    {
        $foreignAddon = Addon::factory()->create();

        $this->post(route('booking.store'), $this->bookingData(['addons' => [$foreignAddon->id]]))
            ->assertSessionHasErrors('addons.0');
    }

    public function test_scheduler_rechecks_availability_inside_the_lock(): void
    {
        $client = Client::factory()->create();
        $scheduler = app(AppointmentScheduler::class);
        $startsAt = CarbonImmutable::parse('2026-10-20 10:00');

        $scheduler->book($client, $this->service, collect(), $startsAt);

        $this->expectException(SlotUnavailableException::class);
        $scheduler->book($client, $this->service, collect(), $startsAt->addMinutes(30));
    }

    public function test_confirmation_page_uses_the_public_code(): void
    {
        $appointment = Appointment::factory()->create();

        $this->get('/turno/'.$appointment->public_code)->assertOk()->assertSee($appointment->service->name);
        $this->get('/turno/'.$appointment->id)->assertNotFound();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function bookingData(array $overrides = []): array
    {
        return [
            'service_id' => $this->service->id,
            'addons' => [],
            'date' => '2026-10-20',
            'time' => '10:00',
            'name' => 'Lucía Gómez',
            'phone' => '54 9 11 2345-6789',
            ...$overrides,
        ];
    }
}
