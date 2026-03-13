<?php

it('kiosk config has separate password keys', function () {
    expect(config('kiosk.kiosk_password'))->toBeNull()
        ->and(config('kiosk.tv_display_password'))->toBeNull();
});

it('kiosk config has default session lifetime', function () {
    expect(config('kiosk.session_lifetime'))->toBe(1440);
});
