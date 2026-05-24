<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('users.{id}', function ($user, int $id) {
    return (int) $user->id === $id;
});

Broadcast::channel('conversations.{conversation}', function ($user, int $conversation) {
    return Conversation::query()
        ->whereKey($conversation)
        ->forUser($user)
        ->exists();
});
