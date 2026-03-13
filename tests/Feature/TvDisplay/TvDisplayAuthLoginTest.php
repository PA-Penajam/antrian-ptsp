<?php

use function Pest\Laravel\post;

it('logs in with hashed tv-display password', function () {
    config(['kiosk.tv_display_password' => bcrypt('test-tv-pass')]);

    $response = post(route('tv-display.authenticate'), [
        'password' => 'test-tv-pass',
    ]);

    $response->assertRedirect(route('tv-display.index'))
        ->assertSessionHas('tv_display_authenticated', true);
});

it('rejects wrong tv-display password', function () {
    config(['kiosk.tv_display_password' => bcrypt('correct-pass')]);

    $response = post(route('tv-display.authenticate'), [
        'password' => 'wrong-pass',
    ]);

    $response->assertSessionHasErrors(['password']);
});
