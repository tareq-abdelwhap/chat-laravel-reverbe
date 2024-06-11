<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class ChatEvent implements ShouldBroadcastNow 
{
    use InteractsWithSockets;

    public function __construct(
        public $message
    ){}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel("chat.{$this->message->receiver_id}");
    }
}
