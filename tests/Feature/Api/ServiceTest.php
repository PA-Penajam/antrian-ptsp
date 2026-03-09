<?php

use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

test('can list active services', function () {
    Service::factory()->create(['is_active' => true, 'booking_enabled' => true]);
    Service::factory()->create(['is_active' => false]);
    $response = getJson('/api/services');
    $response->assertOk()
        ->assertJsonStructure(['data' => [['id', 'name', 'slug', 'booking_enabled']]])
        ->assertJsonCount(1, 'data');
});

test('can get service by slug', function () {
    $service = Service::factory()->create(['is_active' => true]);
    $response = getJson('/api/services/'.$service->slug);
    $response->assertOk()
        ->assertJsonPath('data.slug', $service->slug);
});

test('returns 404 for inactive service slug', function () {
    $service = Service::factory()->create(['is_active' => false]);
    $response = getJson('/api/services/'.$service->slug);
    $response->assertNotFound();
});

test('returns 404 for non-existent slug', function () {
    $response = getJson('/api/services/does-not-exist-xyz-999');
    $response->assertNotFound();
});
