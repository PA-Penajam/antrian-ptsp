<?php

it('kiosk config has default password', function () {
    expect(config('kiosk.password'))->toBe('ptsp2024');
});

it('kiosk config has default session lifetime', function () {
    expect(config('kiosk.session_lifetime'))->toBe(1440);
});
