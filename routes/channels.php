<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);
    if (!$conversation) {
        return false;
    }

    if ($user instanceof \App\Models\Vendor) {
        return (int) $conversation->vendor_id === (int) $user->id;
    }

    return (int) $conversation->user_id === (int) $user->id;
}, ['guards' => ['web', 'vendor']]);
