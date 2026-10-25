<?php

namespace App\Support;

class Money
{
    /**
     * Format a price in Argentine style, e.g. 16000 => "$ 16.000".
     */
    public static function format(float|int|string $amount): string
    {
        return '$ '.number_format((float) $amount, 0, ',', '.');
    }
}
