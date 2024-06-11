<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;
use App\Broadcasting\MessageChannel;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{message}', MessageChannel::class);
Broadcast::channel('online-status', function ($user) {
    return $user;
});
