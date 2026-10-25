@php
    use App\Enums\AppointmentStatus;

    $nextActions = match ($appointment->status) {
        AppointmentStatus::Pending => [[AppointmentStatus::Confirmed, 'Confirmar'], [AppointmentStatus::Cancelled, 'Cancelar']],
        AppointmentStatus::Confirmed => [[AppointmentStatus::Completed, 'Completar'], [AppointmentStatus::Cancelled, 'Cancelar']],
        default => [],
    };
@endphp

@foreach ($nextActions as [$status, $label])
    <form method="POST" action="{{ route('admin.appointments.status', $appointment) }}"
          @if ($status === AppointmentStatus::Cancelled) onsubmit="return confirm('¿Cancelar este turno? El horario queda libre para otra clienta.')" @endif>
        @csrf
        @method('PATCH')
        <input type="hidden" name="status" value="{{ $status->value }}">
        <button @class(['btn py-1.5', 'btn-danger' => $status === AppointmentStatus::Cancelled, 'btn-primary' => $status !== AppointmentStatus::Cancelled])>{{ $label }}</button>
    </form>
@endforeach
