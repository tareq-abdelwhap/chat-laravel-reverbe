<?php

namespace App\Broadcasting;

use App\Models\User;
use App\Models\Message;

class MessageChannel
{
    /**
     * Create a new channel instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Authenticate the user's access to the channel.
     */
    public function join(User $user): array|bool
    {
        return [$user->id];
        // return (int) $user->id === (int) $message->receiver_id;
    }
}
