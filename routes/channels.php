<?php

use App\Models\GroupMember;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// DM channel — user must be one of the two participants
Broadcast::channel('dm.{minId}.{maxId}', function ($user, $minId, $maxId) {
    return in_array($user->id, [(int) $minId, (int) $maxId]);
});

// Group channel — user must be an Accepted member
Broadcast::channel('group.{groupId}', function ($user, $groupId) {
    return GroupMember::where('group_id', $groupId)
        ->where('user_id', $user->id)
        ->where('status', GroupMember::STATUS_ACCEPTED)
        ->exists();
});
