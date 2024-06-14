<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Support\Facades\Auth;

class OnlineStatus implements ShouldBroadcastNow 
{
    use InteractsWithSockets;

    public function __construct(
        public $user
    ){}

    public function broadcastOn(): Channel
    {
        return new PresenceChannel("online-status");
    }

    public function broadcastWith()
    {
        return ['user' => $this->user];
    }

}
