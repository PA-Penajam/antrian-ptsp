<?php

use App\Actions\Queue\CheckPrinterConnectivity;
use App\Actions\Queue\PrintTicketToEposPrinter;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Mockery\MockInterface;

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
        ->assertSee('SILAKAN PILIH LAYANAN');
});

it('renders kiosk legacy page without browser-side printer code', function () {
    Service::factory()->create([
        'is_active' => true,
        'walk_in_enabled' => true,
    ]);

    $response = withSession(kioskLegacySession())
        ->get(route('kiosk.legacy'));

    $response->assertOk()
        ->assertDontSee('initPrinter', false)
        ->assertDontSee('eposPrinter', false)
        ->assertDontSee('printerInitInProgress', false)
        ->assertSee('res.printed', false);
});

function kioskLegacyPostData(int $serviceId, string $wilayahKode): array
{
    return [
        'service_id' => $serviceId,
        'visitor_name' => 'Budi Santoso',
        'visitor_identifier' => '1234567890123456',
        'visitor_phone' => '08123456789',
        'visitor_wilayah_kode' => $wilayahKode,
    ];
}

it('printLegacy response includes printed true when printer succeeds', function () {
    $service = Service::factory()->create([
        'is_active' => true,
        'walk_in_enabled' => true,
    ]);
    DB::table('wilayah')->insert(['kode' => '64.03.01.2001', 'nama' => 'Desa Test']);

    $this->mock(PrintTicketToEposPrinter::class, function (MockInterface $mock) {
        $mock->shouldReceive('handle')->once()->andReturn(true);
    });

    $response = withSession(kioskLegacySession())
        ->postJson(route('kiosk.legacy.print'), kioskLegacyPostData($service->id, '64.03.01.2001'));

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('printed', true)
        ->assertJsonStructure(['success', 'ticket', 'printed']);
});

it('printLegacy response includes printed false when printer fails', function () {
    $service = Service::factory()->create([
        'is_active' => true,
        'walk_in_enabled' => true,
    ]);
    DB::table('wilayah')->insert(['kode' => '64.03.01.2001', 'nama' => 'Desa Test']);

    $this->mock(PrintTicketToEposPrinter::class, function (MockInterface $mock) {
        $mock->shouldReceive('handle')->once()->andReturn(false);
    });

    $response = withSession(kioskLegacySession())
        ->postJson(route('kiosk.legacy.print'), kioskLegacyPostData($service->id, '64.03.01.2001'));

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('printed', false);
});

it('printLegacy creates ticket even when printer fails', function () {
    $service = Service::factory()->create([
        'is_active' => true,
        'walk_in_enabled' => true,
    ]);
    DB::table('wilayah')->insert(['kode' => '64.03.01.2001', 'nama' => 'Desa Test']);

    $this->mock(PrintTicketToEposPrinter::class, function (MockInterface $mock) {
        $mock->shouldReceive('handle')->once()->andReturn(false);
    });

    withSession(kioskLegacySession())
        ->postJson(route('kiosk.legacy.print'), kioskLegacyPostData($service->id, '64.03.01.2001'));

    $this->assertDatabaseHas('queue_tickets', [
        'visitor_name' => 'Budi Santoso',
    ]);
});

// ── Printer Status Endpoint ───────────────────────────────────────────────

it('printer status endpoint requires kiosk session', function () {
    $this->get(route('kiosk.legacy.printer-status'))
        ->assertRedirect(route('kiosk.legacy.login'));
});

it('printer status returns json with required fields when connected', function () {
    config([
        'services.thermal_printer.enabled' => true,
        'services.thermal_printer.ip' => '192.168.10.27',
        'services.thermal_printer.port' => 8008,
    ]);

    $this->mock(CheckPrinterConnectivity::class, function (MockInterface $mock) {
        $mock->shouldReceive('handle')->once()->andReturn(['connected' => true, 'error' => null]);
    });

    withSession(kioskLegacySession())
        ->get(route('kiosk.legacy.printer-status'))
        ->assertOk()
        ->assertJsonStructure(['status', 'ip', 'port', 'checked_at', 'error'])
        ->assertJson([
            'status' => 'connected',
            'ip' => '192.168.10.27',
            'port' => 8008,
            'error' => null,
        ]);
});

it('printer status returns disconnected with error when connectivity check fails', function () {
    config(['services.thermal_printer.enabled' => true]);

    $this->mock(CheckPrinterConnectivity::class, function (MockInterface $mock) {
        $mock->shouldReceive('handle')->once()->andReturn([
            'connected' => false,
            'error' => 'cURL error 28: Operation timed out',
        ]);
    });

    withSession(kioskLegacySession())
        ->get(route('kiosk.legacy.printer-status'))
        ->assertOk()
        ->assertJson([
            'status' => 'disconnected',
            'error' => 'cURL error 28: Operation timed out',
        ]);
});

it('printer status returns disabled when thermal printer config is off', function () {
    config(['services.thermal_printer.enabled' => false]);

    withSession(kioskLegacySession())
        ->get(route('kiosk.legacy.printer-status'))
        ->assertOk()
        ->assertJson(['status' => 'disabled']);
});

// ── Status Bar View ───────────────────────────────────────────────────────

it('renders printer status bar with polling js in legacy page', function () {
    Service::factory()->create(['is_active' => true, 'walk_in_enabled' => true]);

    withSession(kioskLegacySession())
        ->get(route('kiosk.legacy'))
        ->assertOk()
        ->assertSee('printerStatusBar', false)
        ->assertSee('checkPrinterStatus', false)
        ->assertSee('showPrinterFlash', false)
        ->assertSee('printer-status', false);
});

// ── Legacy Login & PIN Authentication ───────────────────────────────────────

it('renders kiosk legacy login page with PIN and on-screen numpad', function () {
    $response = $this->get(route('kiosk.legacy.login'));

    $response->assertOk()
        ->assertSee('Akses Kiosk Legacy')
        ->assertSee('PIN Kiosk')
        ->assertSee('Masukkan PIN Kiosk...')
        ->assertSee('Papan Tombol PIN')
        ->assertSee('login-numpad-btn', false);
});

it('logs in to legacy kiosk with correct PIN', function () {
    config(['kiosk.kiosk_password' => bcrypt('123456')]);

    $response = $this->post(route('kiosk.legacy.authenticate'), [
        'password' => '123456',
    ]);

    $response->assertRedirect(route('kiosk.legacy'))
        ->assertSessionHas('kiosk_authenticated', true);
});

it('rejects wrong PIN on legacy kiosk login', function () {
    config(['kiosk.kiosk_password' => bcrypt('123456')]);

    $response = $this->from(route('kiosk.legacy.login'))
        ->post(route('kiosk.legacy.authenticate'), [
            'password' => '000000',
        ]);

    $response->assertRedirect(route('kiosk.legacy.login'))
        ->assertSessionHasErrors(['password']);
});
