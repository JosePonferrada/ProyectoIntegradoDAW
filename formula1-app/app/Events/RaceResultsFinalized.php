<?php

namespace App\Events;

use App\Models\Race;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RaceResultsFinalized
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Race $race;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Race $race
     * @return void
     */
    public function __construct(Race $race)
    {
        $this->race = $race;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'), // Opcional, si necesitas broadcasting
        ];
    }
}
