<?php

use function Pest\Laravel\post;

it('rate limits kiosk login after 5 attempts', function () {
    config(['kiosk.kiosk_password' => bcrypt('correct')]);

    for ($i = 0; $i < 5; $i++) {
        post(route('kiosk.authenticate'), ['password' => 'wrong']);
    }

    $response = post(route('kiosk.authenticate'), ['password' => 'wrong']);

    $response->assertStatus(429);
});

it('rate limits tv-display login after 5 attempts', function () {
    config(['kiosk.tv_display_password' => bcrypt('correct')]);

    for ($i = 0; $i < 5; $i++) {
        post(route('tv-display.authenticate'), ['password' => 'wrong']);
    }

    $response = post(route('tv-display.authenticate'), ['password' => 'wrong']);

    $response->assertStatus(429);
});
