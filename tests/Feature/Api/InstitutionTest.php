<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can get institution info', function () {
    $response = $this->getJson('/api/institution');

    $response->assertOk()
        ->assertJsonStructure(['name', 'address', 'phone', 'operating_hours']);
});

test('institution endpoint returns 405 for non-GET methods', function () {
    $response = $this->postJson('/api/institution');

    $response->assertMethodNotAllowed();
});
