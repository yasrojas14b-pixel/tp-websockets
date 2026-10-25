<?php

namespace App\Http\Requests\Admin;

use App\Enums\AppointmentStatus;
use App\Models\Addon;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class AppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_id' => ['required', Rule::exists('clients', 'id')],
            'service_id' => ['required', Rule::exists('services', 'id')],
            'addons' => ['array'],
            'addons.*' => ['integer', Rule::exists('addon_service', 'addon_id')->where('service_id', $this->integer('service_id'))],
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'date_format:H:i'],
            'status' => ['required', Rule::enum(AppointmentStatus::class)],
            'client_notes' => ['nullable', 'string', 'max:500'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
            'ignore_business_hours' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'client_id' => 'clienta',
            'service_id' => 'servicio',
            'addons.*' => 'adicional',
            'date' => 'fecha',
            'time' => 'horario',
            'status' => 'estado',
        ];
    }

    public function service(): Service
    {
        return Service::findOrFail($this->integer('service_id'));
    }

    /**
     * @return Collection<int, Addon>
     */
    public function addons(): Collection
    {
        return Addon::query()->whereKey($this->input('addons', []))->get();
    }

    public function startsAt(): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('Y-m-d H:i', $this->input('date').' '.$this->input('time'));
    }

    /**
     * @return array{status: AppointmentStatus, client_id: int, client_notes: ?string, admin_notes: ?string}
     */
    public function appointmentAttributes(): array
    {
        return [
            'status' => $this->enum('status', AppointmentStatus::class),
            'client_id' => $this->integer('client_id'),
            'client_notes' => $this->input('client_notes'),
            'admin_notes' => $this->input('admin_notes'),
        ];
    }
}
