<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/*
 * Public broadcast channel consumed by kiosks, TV displays, and the
 * antrian-public SPA. Authentication is not required; returning true
 * simply documents the channel explicitly alongside the authenticated
 * channels in this file.
 */
Broadcast::channel('public-queue', static fn (): bool => true);
