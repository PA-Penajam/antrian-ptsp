<?php

use App\Livewire\KioskBooking;
use App\Models\AppSetting;
use App\Models\QueuePool;
use App\Models\QueueTicket;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

use function Pest\Laravel\withSession;

function kioskSession(): array
{
    return [
        'kiosk_authenticated' => true,
        'kiosk_authenticated_at' => now()->timestamp,
    ];
}

/**
 * @return array{kode:string,nama:string}
 */
function sampleWilayahDesa(): array
{
    $wilayah = [
        'kode' => '64.09.01.1001',
        'nama' => 'Penajam',
    ];

    DB::table('wilayah')->updateOrInsert(
        ['kode' => $wilayah['kode']],
        ['nama' => $wilayah['nama']]
    );

    return $wilayah;
}

it('renders kiosk booking page when authenticated', function () {
    Service::factory()->create([
        'is_active' => true,
        'walk_in_enabled' => true,
    ]);

    $response = withSession(kioskSession())
        ->get(route('kiosk.index'));

    $response->assertOk()
        ->assertSee('Pilih Layanan');
});

it('shows active walk_in_enabled services as cards', function () {
    $pool = QueuePool::factory()->create(['letter_code' => 'PC']);
    Service::factory()->create([
        'name' => 'Permohonan Cerai',
        'queue_pool_id' => $pool->id,
        'is_active' => true,
        'walk_in_enabled' => true,
    ]);

    Service::factory()->create([
        'name' => 'Inactive Service',
        'is_active' => false,
        'walk_in_enabled' => true,
    ]);

    Service::factory()->create([
        'name' => 'Online Only Service',
        'is_active' => true,
        'walk_in_enabled' => false,
    ]);

    // Start session for Livewire test
    session($session = kioskSession());

    $component = Livewire::test(KioskBooking::class);

    $component->assertSee('Permohonan Cerai')
        ->assertSee('PC')
        ->assertDontSee('Inactive Service')
        ->assertDontSee('Online Only Service');
});

it('moves to step 2 when service is selected', function () {
    $service = Service::factory()->create([
        'is_active' => true,
        'walk_in_enabled' => true,
    ]);

    session(kioskSession());

    $component = Livewire::test(KioskBooking::class);

    $component->assertSet('step', 1)
        ->call('selectService', $service->id)
        ->assertSet('step', 2)
        ->assertSet('selectedServiceId', $service->id);
});

it('validates visitor name is required', function () {
    session(kioskSession());

    $component = Livewire::test(KioskBooking::class);

    $component->set('step', 2)
        ->set('visitorName', '')
        ->call('submitData')
        ->assertHasErrors(['visitorName' => 'required']);
});

it('validates visitor name minimum length', function () {
    session(kioskSession());

    $component = Livewire::test(KioskBooking::class);

    $component->set('step', 2)
        ->set('visitorName', 'ab')
        ->call('submitData')
        ->assertHasErrors(['visitorName' => 'min']);
});

it('validates wilayah is required', function () {
    session(kioskSession());

    $component = Livewire::test(KioskBooking::class);

    $component->set('step', 2)
        ->set('visitorName', 'Pengunjung Tes')
        ->set('visitorWilayahKode', null)
        ->call('submitData')
        ->assertHasErrors([
            'visitorWilayahKode' => 'required',
        ]);
});

it('rejects wilayah outside selected kabupaten scope', function () {
    DB::table('wilayah')->insert([
        ['kode' => '64.09', 'nama' => 'Kabupaten Penajam Paser Utara'],
        ['kode' => '64.01.01.1001', 'nama' => 'Desa Luar Scope'],
    ]);
    AppSetting::setValue('wilayah.scope.kabupaten_kode', '64.09');

    session(kioskSession());

    $component = Livewire::test(KioskBooking::class);

    $component->set('step', 2)
        ->set('visitorName', 'Pengunjung Tes')
        ->set('visitorWilayahKode', '64.01.01.1001')
        ->call('submitData')
        ->assertHasErrors(['visitorWilayahKode' => 'exists']);
});

it('moves to step 3 when data is valid', function () {
    $service = Service::factory()->create([
        'is_active' => true,
        'walk_in_enabled' => true,
    ]);
    $wilayah = sampleWilayahDesa();

    session(kioskSession());

    $component = Livewire::test(KioskBooking::class);

    $component->call('selectService', $service->id)
        ->set('visitorName', 'John Doe')
        ->set('visitorIdentifier', '123456789')
        ->set('visitorPhone', '081234567890')
        ->set('visitorWilayahKode', $wilayah['kode'])
        ->set('visitorWilayahNama', $wilayah['nama'])
        ->call('submitData')
        ->assertSet('step', 3);
});

it('creates ticket with walk_in_kiosk channel on confirm', function () {
    $service = Service::factory()->create([
        'name' => 'Test Service',
        'is_active' => true,
        'walk_in_enabled' => true,
    ]);
    $wilayah = sampleWilayahDesa();

    session(kioskSession());

    $component = Livewire::test(KioskBooking::class);

    $component->call('selectService', $service->id)
        ->set('visitorName', 'Jane Doe')
        ->set('visitorIdentifier', '987654321')
        ->set('visitorPhone', '089876543210')
        ->set('visitorWilayahKode', $wilayah['kode'])
        ->set('visitorWilayahNama', $wilayah['nama'])
        ->call('submitData')
        ->call('confirmBooking');

    $component->assertSet('step', 4)
        ->assertSee('Tiket Berhasil Dibuat');

    // Verify ticket was created in database
    $this->assertDatabaseHas('queue_tickets', [
        'service_id' => $service->id,
        'channel' => 'walk_in_kiosk',
        'visitor_name' => 'Jane Doe',
        'visitor_identifier' => '987654321',
        'visitor_phone' => '089876543210',
        'visitor_wilayah_kode' => $wilayah['kode'],
    ]);
});

it('stores identifier and phone when provided', function () {
    $service = Service::factory()->create([
        'is_active' => true,
        'walk_in_enabled' => true,
    ]);
    $wilayah = sampleWilayahDesa();

    session(kioskSession());

    $component = Livewire::test(KioskBooking::class);

    $component->call('selectService', $service->id)
        ->set('visitorName', 'Anonymous User')
        ->set('visitorIdentifier', '1234567890123456')
        ->set('visitorPhone', '081234567890')
        ->set('visitorWilayahKode', $wilayah['kode'])
        ->set('visitorWilayahNama', $wilayah['nama'])
        ->call('submitData')
        ->call('confirmBooking');

    $component->assertSet('step', 4);

    $this->assertDatabaseHas('queue_tickets', [
        'service_id' => $service->id,
        'channel' => 'walk_in_kiosk',
        'visitor_name' => 'Anonymous User',
        'visitor_identifier' => '1234567890123456',
        'visitor_phone' => '081234567890',
        'visitor_wilayah_kode' => $wilayah['kode'],
    ]);
});

it('goes back to previous step', function () {
    $service = Service::factory()->create([
        'is_active' => true,
        'walk_in_enabled' => true,
    ]);

    session(kioskSession());

    $component = Livewire::test(KioskBooking::class);

    $component->call('selectService', $service->id)
        ->assertSet('step', 2)
        ->call('goBack')
        ->assertSet('step', 1)
        ->assertSet('selectedServiceId', null);
});

it('resets wizard to initial state', function () {
    $service = Service::factory()->create([
        'is_active' => true,
        'walk_in_enabled' => true,
    ]);
    $wilayah = sampleWilayahDesa();

    session(kioskSession());

    $component = Livewire::test(KioskBooking::class);

    $component->call('selectService', $service->id)
        ->set('visitorName', 'Test User')
        ->set('visitorIdentifier', '1234567890123456')
        ->set('visitorPhone', '081234567890')
        ->set('visitorWilayahKode', $wilayah['kode'])
        ->set('visitorWilayahNama', $wilayah['nama'])
        ->call('submitData')
        ->assertHasNoErrors()
        ->call('confirmBooking')
        ->assertHasNoErrors()
        ->assertSet('step', 4)
        ->call('resetWizard')
        ->assertSet('step', 1)
        ->assertSet('selectedServiceId', null)
        ->assertSet('visitorName', '')
        ->assertSet('visitorIdentifier', '')
        ->assertSet('visitorPhone', '')
        ->assertSet('visitorWilayahKode', null)
        ->assertSet('visitorWilayahNama', '');
});

// --- Reprint Tests ---

it('shows reprint search form when entering reprint mode', function () {
    session(kioskSession());

    $component = Livewire::test(KioskBooking::class);

    $component->call('enterReprintMode')
        ->assertSet('step', 0)
        ->assertSee('Cetak Ulang Tiket');
});

it('finds ticket by visitor identifier for today', function () {
    $service = Service::factory()->create([
        'is_active' => true,
        'walk_in_enabled' => true,
    ]);
    $ticket = QueueTicket::factory()->for($service)->create([
        'visitor_identifier' => '3507XXXXXXXXXXXX',
        'service_date' => today(),
        'status' => 'waiting',
    ]);

    session(kioskSession());

    $component = Livewire::test(KioskBooking::class);

    $component->call('enterReprintMode')
        ->set('reprintQuery', '3507XXXXXXXXXXXX')
        ->call('searchTicketForReprint')
        ->assertSet('reprintTicket.id', $ticket->id)
        ->assertSee($ticket->ticket_number);
});

it('finds ticket by visitor phone for today', function () {
    $service = Service::factory()->create([
        'is_active' => true,
        'walk_in_enabled' => true,
    ]);
    $ticket = QueueTicket::factory()->for($service)->create([
        'visitor_phone' => '081234567890',
        'service_date' => today(),
        'status' => 'waiting',
    ]);

    session(kioskSession());

    $component = Livewire::test(KioskBooking::class);

    $component->call('enterReprintMode')
        ->set('reprintQuery', '081234567890')
        ->call('searchTicketForReprint')
        ->assertSet('reprintTicket.id', $ticket->id);
});

it('shows not found when no ticket matches reprint query', function () {
    session(kioskSession());

    $component = Livewire::test(KioskBooking::class);

    $component->call('enterReprintMode')
        ->set('reprintQuery', '0000000000000000')
        ->call('searchTicketForReprint')
        ->assertSet('reprintTicket', null)
        ->assertSee('Tiket Tidak Ditemukan');
});

it('ignores tickets from other dates in reprint search', function () {
    $service = Service::factory()->create([
        'is_active' => true,
        'walk_in_enabled' => true,
    ]);
    QueueTicket::factory()->for($service)->create([
        'visitor_identifier' => '3507XXXXXXXXXXXX',
        'service_date' => today()->subDay(),
        'status' => 'waiting',
    ]);

    session(kioskSession());

    $component = Livewire::test(KioskBooking::class);

    $component->call('enterReprintMode')
        ->set('reprintQuery', '3507XXXXXXXXXXXX')
        ->call('searchTicketForReprint')
        ->assertSet('reprintTicket', null);
});

it('returns to step 1 when exiting reprint mode', function () {
    session(kioskSession());

    $component = Livewire::test(KioskBooking::class);

    $component->call('enterReprintMode')
        ->assertSet('step', 0)
        ->call('exitReprintMode')
        ->assertSet('step', 1)
        ->assertSet('reprintQuery', '')
        ->assertSet('reprintTicket', null);
});

it('generates barcode SVG on ticket confirmation', function () {
    $service = Service::factory()->create([
        'is_active' => true,
        'walk_in_enabled' => true,
    ]);
    $wilayah = sampleWilayahDesa();

    session(kioskSession());

    $component = Livewire::test(KioskBooking::class);

    $component->call('selectService', $service->id)
        ->set('visitorName', 'Barcode Test User')
        ->set('visitorIdentifier', '1234567890123456')
        ->set('visitorPhone', '081234567890')
        ->set('visitorWilayahKode', $wilayah['kode'])
        ->set('visitorWilayahNama', $wilayah['nama'])
        ->call('submitData')
        ->call('confirmBooking')
        ->call('loadBarcode');

    $component->assertSet('step', 4)
        ->assertSeeHtml('<svg')
        ->assertNotSet('barcodeSvg', '');
});
