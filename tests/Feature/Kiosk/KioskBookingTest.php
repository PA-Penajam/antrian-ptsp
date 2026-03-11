<?php

use App\Livewire\KioskBooking;
use App\Models\AppSetting;
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
    Service::factory()->create([
        'name' => 'Permohonan Cerai',
        'letter_code' => 'PC',
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

it('allows optional identifier and phone to be empty', function () {
    $service = Service::factory()->create([
        'is_active' => true,
        'walk_in_enabled' => true,
    ]);
    $wilayah = sampleWilayahDesa();

    session(kioskSession());

    $component = Livewire::test(KioskBooking::class);

    $component->call('selectService', $service->id)
        ->set('visitorName', 'Anonymous User')
        ->set('visitorWilayahKode', $wilayah['kode'])
        ->set('visitorWilayahNama', $wilayah['nama'])
        ->call('submitData')
        ->call('confirmBooking');

    $component->assertSet('step', 4);

    $this->assertDatabaseHas('queue_tickets', [
        'service_id' => $service->id,
        'channel' => 'walk_in_kiosk',
        'visitor_name' => 'Anonymous User',
        'visitor_identifier' => null,
        'visitor_phone' => null,
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
        ->set('visitorWilayahKode', $wilayah['kode'])
        ->set('visitorWilayahNama', $wilayah['nama'])
        ->call('submitData')
        ->call('confirmBooking')
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
        ->set('visitorWilayahKode', $wilayah['kode'])
        ->set('visitorWilayahNama', $wilayah['nama'])
        ->call('submitData')
        ->call('confirmBooking')
        ->call('loadBarcode');

    $component->assertSet('step', 4)
        ->assertSeeHtml('<svg')
        ->assertNotSet('barcodeSvg', '');
});
