<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Models\Addon;
use App\Models\Appointment;
use App\Models\BusinessHour;
use App\Models\Client;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class SalonSeeder extends Seeder
{
    /**
     * NailsYasmin catalog, opening hours and a few demo appointments.
     */
    public function run(): void
    {
        $services = collect([
            ['Manicuría tradicional', 'Limado, cutícula, hidratación y esmaltado común.', 8000, 45],
            ['Esmaltado semipermanente', 'Esmaltado en gel curado con lámpara. Dura de 2 a 3 semanas.', 12000, 60],
            ['Kapping en gel', 'Capa de gel sobre la uña natural para darle fuerza. Incluye color.', 16000, 90],
            ['Esculpidas en gel', 'Extensión con molde o tips y gel constructor. Incluye color liso.', 22000, 120],
            ['Esculpidas en acrílico', 'Extensión en polímero acrílico. Incluye color liso.', 22000, 120],
            ['Service de esculpidas', 'Mantenimiento de esculpidas propias (hasta 21 días).', 15000, 90],
            ['Belleza de pies', 'Pedicuría estética con semipermanente.', 14000, 60],
        ])->mapWithKeys(fn (array $service, int $index): array => [
            $service[0] => Service::create([
                'name' => $service[0],
                'description' => $service[1],
                'price' => $service[2],
                'duration_minutes' => $service[3],
                'sort_order' => $index,
            ]),
        ]);

        $addons = [
            ['Retirado de material anterior', 'Retiro de semi, kapping o esculpidas de otro salón.', 3000, 20, ['Esmaltado semipermanente', 'Kapping en gel', 'Esculpidas en gel', 'Esculpidas en acrílico']],
            ['Nail art simple', 'Diseño en 2 uñas: puntos, líneas, stickers.', 2000, 15, ['Esmaltado semipermanente', 'Kapping en gel', 'Esculpidas en gel', 'Esculpidas en acrílico', 'Service de esculpidas']],
            ['Nail art complejo', 'Diseño a mano alzada, encapsulado o 3D en todas las uñas.', 6000, 45, ['Kapping en gel', 'Esculpidas en gel', 'Esculpidas en acrílico', 'Service de esculpidas']],
            ['Francesita', 'Punta francesa clásica o de color.', 2500, 15, ['Esmaltado semipermanente', 'Kapping en gel', 'Esculpidas en gel', 'Esculpidas en acrílico', 'Service de esculpidas']],
            ['Reparación de uña', 'Reconstrucción de una uña rota.', 1500, 10, ['Kapping en gel', 'Service de esculpidas']],
        ];

        foreach ($addons as [$name, $description, $price, $minutes, $serviceNames]) {
            Addon::create([
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'duration_minutes' => $minutes,
            ])->services()->attach($services->only($serviceNames)->pluck('id'));
        }

        /*
         * Monday to Friday 09:00-13:00 and 14:00-19:00 (lunch break 13-14),
         * Saturday 09:00-14:00, Sunday closed.
         */
        foreach ([1, 2, 3, 4, 5] as $weekday) {
            BusinessHour::create(['weekday' => $weekday, 'opens_at' => '09:00', 'closes_at' => '13:00']);
            BusinessHour::create(['weekday' => $weekday, 'opens_at' => '14:00', 'closes_at' => '19:00']);
        }
        BusinessHour::create(['weekday' => 6, 'opens_at' => '09:00', 'closes_at' => '14:00']);

        $clients = Client::factory()->count(12)->create();
        $clients->first()->update([
            'allergies' => 'Alergia al HEMA: usar línea hipoalergénica.',
            'internal_notes' => 'Prefiere turnos a la mañana. Siempre trae foto de referencia.',
        ]);

        $nextWorkday = CarbonImmutable::tomorrow();
        while (! in_array($nextWorkday->dayOfWeek, [1, 2, 3, 4, 5], true)) {
            $nextWorkday = $nextWorkday->addDay();
        }

        $demo = [
            [$nextWorkday->setTime(9, 0), 'Kapping en gel', AppointmentStatus::Confirmed],
            [$nextWorkday->setTime(10, 30), 'Esmaltado semipermanente', AppointmentStatus::Pending],
            [$nextWorkday->setTime(14, 0), 'Esculpidas en gel', AppointmentStatus::Confirmed],
            [$nextWorkday->addDay()->setTime(11, 0), 'Service de esculpidas', AppointmentStatus::Pending],
            [CarbonImmutable::today()->subDays(7)->setTime(10, 0), 'Kapping en gel', AppointmentStatus::Completed],
            [CarbonImmutable::today()->subDays(3)->setTime(15, 0), 'Esmaltado semipermanente', AppointmentStatus::Completed],
        ];

        foreach ($demo as $index => [$startsAt, $serviceName, $status]) {
            $service = $services[$serviceName];

            Appointment::create([
                'client_id' => $clients[$index % $clients->count()]->id,
                'service_id' => $service->id,
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->addMinutes($service->duration_minutes),
                'status' => $status,
                'total_price' => $service->price,
                'source' => $index % 2 === 0 ? 'online' : 'admin',
            ]);
        }
    }
}
