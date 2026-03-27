<?php

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
        ->assertSee('SILAKAN PILIH LAYANAN')
        ->assertDontSee('function printTicket(ticketData)', false)
        ->assertDontSee('function initPrinter(callback)', false);
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
