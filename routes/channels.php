<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;

Broadcast::channel('chat.{receiver_id}', fn (User $user, $receiver_id) => (int)$user->id === (int)$receiver_id);
Broadcast::channel('online-status', fn ($user) => $user);
