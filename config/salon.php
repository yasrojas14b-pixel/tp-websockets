<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Salon Settings
    |--------------------------------------------------------------------------
    |
    | Business rules for the NailsYasmin booking system.
    |
    */

    'name' => env('SALON_NAME', 'NailsYasmin'),

    'whatsapp' => env('SALON_WHATSAPP', '3704073805'),

    'address' => env('SALON_ADDRESS', 'Formosa Capital, Argentina'),

    /*
     * Full Instagram profile URL. The footer link is hidden when empty.
     */
    'instagram' => env('SALON_INSTAGRAM'),

    /*
     * Offered start times are aligned to this grid (09:00, 09:15, 09:30...).
     */
    'slot_interval_minutes' => (int) env('SALON_SLOT_INTERVAL', 15),

    /*
     * Clients cannot book a slot that starts sooner than this from now.
     */
    'min_notice_minutes' => (int) env('SALON_MIN_NOTICE', 120),

    /*
     * How many days ahead the public booking page shows.
     */
    'booking_window_days' => (int) env('SALON_BOOKING_WINDOW', 45),

];
