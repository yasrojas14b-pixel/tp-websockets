<?php

namespace App\Events;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class AppointmentStatusUpdated implements ShouldBroadcastNow
{
    public string $publicCode;
    public string $status;
    public string $label;
    public string $badgeClasses;

    public function __construct(Appointment $appointment)
    {
        $this->publicCode = $appointment->public_code;
        $this->status = $appointment->status->value;
        $this->label = $appointment->status->label();
        $this->badgeClasses = $appointment->status->badgeClasses();
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('appointment-status.' . $this->publicCode),
        ];
    }

    public function broadcastAs(): string
    {
        return 'AppointmentStatusUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'status' => $this->status,
            'label' => $this->label,
            'badgeClasses' => $this->badgeClasses,
        ];
    }
}