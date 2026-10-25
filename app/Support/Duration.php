<?php

namespace App\Support;

class Duration
{
    /**
     * Format minutes as a short human label, e.g. 90 => "1h 30m".
     */
    public static function format(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        return match (true) {
            $hours === 0 => "{$remainingMinutes}m",
            $remainingMinutes === 0 => "{$hours}h",
            default => "{$hours}h {$remainingMinutes}m",
        };
    }
}
