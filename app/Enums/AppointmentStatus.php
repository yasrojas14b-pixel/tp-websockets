<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::Confirmed => 'Confirmado',
            self::Completed => 'Completado',
            self::Cancelled => 'Cancelado',
        };
    }

    /**
     * Hex color used by the admin calendar.
     */
    public function color(): string
    {
        return match ($this) {
            self::Pending => '#f59e0b',
            self::Confirmed => '#db2777',
            self::Completed => '#10b981',
            self::Cancelled => '#9ca3af',
        };
    }

    /**
     * Tailwind classes for the status badge.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-100 text-amber-800',
            self::Confirmed => 'bg-pink-100 text-pink-800',
            self::Completed => 'bg-emerald-100 text-emerald-800',
            self::Cancelled => 'bg-gray-100 text-gray-600',
        };
    }

    /**
     * Statuses that keep the time slot occupied.
     *
     * @return list<self>
     */
    public static function occupying(): array
    {
        return [self::Pending, self::Confirmed, self::Completed];
    }
}
