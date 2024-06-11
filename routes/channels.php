<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;

Broadcast::channel('chat.{receiver_id}', fn (User $user, $receiver_id) => true);
Broadcast::channel('online-status', fn ($user) => $user);
