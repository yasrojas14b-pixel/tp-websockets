<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

/**
 * Tells open booking pages and the admin calendar that a day changed,
 * so they reload availability. Only the date is sent because the
 * channel is public: no client data travels through it.
 */
class AgendaUpdated implements ShouldBroadcastNow
{
    /**
     * @param  list<string>  $dates  Affected days as Y-m-d.
     */
    public function __construct(public array $dates) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel('agenda')];
    }

    public function broadcastAs(): string
    {
        return 'AgendaUpdated';
    }
}
