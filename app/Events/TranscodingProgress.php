<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class TranscodingProgress implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $progress;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($video_id, $progress)
    {
        $this->progress = $progress;
        $this->$video_id = $video_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('transcoding-progress');
    }

    public function broadcastWith()
    {
        return [
            "progress" => $this->progress,
            "video_id" => $this->$video_id
        ];
    }
}
