<?php

use App\Models\Service;

use function Pest\Laravel\withSession;

function kioskLegacySession(): array
{
    return [
        'kiosk_authenticated' => true,
        'kiosk_authenticated_at' => now()->timestamp,
    ];
}

it('renders kiosk legacy page when authenticated', function () {
    Service::factory()->create([
        'is_active' => true,
        'walk_in_enabled' => true,
    ]);

    $response = withSession(kioskLegacySession())
        ->get(route('kiosk.legacy'));

    $response->assertOk()
        ->assertSee('SILAKAN PILIH LAYANAN')
        ->assertSee('function printTicket(ticketData)')
        ->assertSee('function initPrinter(callback)', false);
});

it('does not auto connect the legacy printer on page load', function () {
    Service::factory()->create([
        'is_active' => true,
        'walk_in_enabled' => true,
    ]);

    $response = withSession(kioskLegacySession())
        ->get(route('kiosk.legacy'));

    $response->assertOk()
        ->assertDontSee('initPrinter();', false)
        ->assertSee('var printerInitInProgress = false;', false)
        ->assertSee('showPrinterWarning(failureCode);', false);
});
