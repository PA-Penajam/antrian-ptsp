<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('api test infrastructure is working', function () {
    expect(true)->toBeTrue();
});
