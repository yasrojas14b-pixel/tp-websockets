<?php

namespace App\Http\Requests;

use App\Models\Service;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'service_id' => ['required', Rule::exists('services', 'id')->where('is_active', true)],
            'addons' => ['array'],
            'addons.*' => ['integer', Rule::exists('addon_service', 'addon_id')->where('service_id', $this->integer('service_id'))],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today', 'before_or_equal:'.now()->addDays(config('salon.booking_window_days'))->toDateString()],
            'time' => ['required', 'date_format:H:i'],
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'min:8', 'max:30', 'regex:/^[\d\s\+\-\(\)]+$/'],
            'client_notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'service_id' => 'servicio',
            'addons.*' => 'adicional',
            'date' => 'fecha',
            'time' => 'horario',
            'name' => 'nombre',
            'phone' => 'teléfono',
            'client_notes' => 'comentarios',
        ];
    }

    public function service(): Service
    {
        return Service::findOrFail($this->integer('service_id'));
    }
}
