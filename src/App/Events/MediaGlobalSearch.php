<?php

namespace ctf0\MediaManager\App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class MediaGlobalSearch implements ShouldBroadcastNow
{
    protected $user;
    public $data;

    /**
     * Create a new event instance.
     *
     * @param mixed $data
     */
    public function __construct($data)
    {
        $this->user = auth()->user();
        $this->data = $data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $id = optional($this->user)->id ?? 0;

        return new PrivateChannel("User.{$id}.media");
    }

    public function broadcastAs()
    {
        return 'user.media.search';
    }
}
