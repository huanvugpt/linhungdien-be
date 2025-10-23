<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// User notification channel - PrivateChannel adds 'private-' prefix automatically
// So this 'user.{userId}' will become 'private-user.{userId}' for Pusher
Broadcast::channel('user.{userId}', function (User $user, int $userId) {
    \Log::info('Channel auth attempt', ['user_id' => $user->id, 'channel_user_id' => $userId]);
    
    // Always allow for testing
    return ['id' => $user->id, 'name' => $user->name];
});