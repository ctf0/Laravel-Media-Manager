<?php

namespace ctf0\MediaManager\App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class MediaFileOpsNotifications implements ShouldBroadcastNow
{
    use InteractsWithSockets;

    public $data;

    /**
     * Create a new event instance.
     *
     * @param mixed $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('User.media');
    }

    public function broadcastAs()
    {
        return 'user.media.ops';
    }
}
