<?php

use App\Enums\QueueStatus;
use App\Enums\UserRole;
use App\Exports\LaporanBulananExport;
use App\Livewire\Reports\LaporanBulanan;
use App\Models\QueuePool;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\User;
use App\Models\Wilayah;
use App\Support\Reports\LaporanBulananReportBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

use function Pest\Laravel\actingAs;

test('admin dapat mengakses halaman laporan bulanan', function () {
    $user = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);

    actingAs($user)
        ->get('/laporan/bulanan')
        ->assertOk();
});

test('monitor dapat mengakses halaman laporan bulanan', function () {
    $user = User::factory()->create([
        'role' => UserRole::Monitor->value,
        'email_verified_at' => now(),
    ]);

    actingAs($user)
        ->get('/laporan/bulanan')
        ->assertOk();
});

test('frontdesk tidak dapat mengakses halaman laporan bulanan', function () {
    $user = User::factory()->create([
        'role' => UserRole::Frontdesk->value,
        'email_verified_at' => now(),
    ]);

    actingAs($user)
        ->get('/laporan/bulanan')
        ->assertForbidden();
});

test('report builder menggunakan label hari bahasa indonesia', function () {
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create();

    QueueTicket::factory()->for($service)->for($pool)->create([
        'service_date' => '2026-04-14',
        'status' => QueueStatus::Completed,
        'channel' => 'online_booking',
        'completed_at' => '2026-04-14 10:00:00',
    ]);

    $report = app(LaporanBulananReportBuilder::class)->build(4, 2026);

    expect(collect($report['per_hari'])->firstWhere('date', '2026-04-14')['nama_hari'])->toBe('Sel');
});

test('export excel menghasilkan file xlsx yang valid', function () {
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create();

    QueueTicket::factory()->count(3)->for($service)->for($pool)->create([
        'service_date' => '2026-04-14',
        'status' => QueueStatus::Completed,
        'channel' => 'online_booking',
        'completed_at' => '2026-04-14 10:00:00',
    ]);

    $report = app(LaporanBulananReportBuilder::class)->build(4, 2026);
    $export = new LaporanBulananExport($report);

    $response = Excel::download($export, 'test.xlsx');

    expect($response->getFile()->getExtension())->toBe('xlsx');
});

test('export pdf menghasilkan file pdf yang valid', function () {
    $pdf = Pdf::loadView('pdf.laporan-bulanan', [
        'judulBulan' => 'April 2026',
        'ringkasan' => ['total' => 1, 'completed' => 1, 'waiting' => 0, 'cancelled' => 0],
        'perLayanan' => [['name' => 'Layanan Uji', 'total' => 1, 'completed' => 1, 'cancelled' => 0]],
        'perHari' => [['date' => '2026-04-14', 'hari' => 14, 'nama_hari' => 'Sel', 'total' => 1, 'online' => 1, 'kiosk' => 0, 'assisted' => 0]],
        'perChannel' => [['channel' => 'Online Booking', 'total' => 1, 'persen' => 100.0]],
    ]);

    $response = $pdf->download('test.pdf');

    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

test('filter bulan menampilkan data yang sesuai', function () {
    $user = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);

    $pool = QueuePool::factory()->create();
    $serviceJanuari = Service::factory()->for($pool)->create(['name' => 'Layanan Januari']);
    $serviceMaret = Service::factory()->for($pool)->create(['name' => 'Layanan Maret']);

    QueueTicket::factory()->for($serviceJanuari)->for($pool)->create([
        'service_date' => '2026-01-15',
        'status' => QueueStatus::Completed,
        'channel' => 'online_booking',
        'completed_at' => '2026-01-15 10:00:00',
    ]);

    QueueTicket::factory()->for($serviceMaret)->for($pool)->create([
        'service_date' => '2026-03-20',
        'status' => QueueStatus::Waiting,
        'channel' => 'walk_in_kiosk',
    ]);

    Livewire::actingAs($user)
        ->test(LaporanBulanan::class)
        ->set('tahun', 2026)
        ->set('bulan', 1)
        ->assertSee('Layanan Januari')
        ->assertDontSee('Layanan Maret')
        ->set('bulan', 3)
        ->assertSee('Layanan Maret')
        ->assertDontSee('Layanan Januari')
        ->set('bulan', 2)
        ->assertSee('Tidak ada data pendaftar untuk bulan ini.');
});

test('cancelled count includes skipped tickets', function () {
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create();

    QueueTicket::factory()->for($service)->for($pool)->create([
        'service_date' => '2026-04-14',
        'status' => QueueStatus::Skipped,
        'channel' => 'online_booking',
    ]);

    QueueTicket::factory()->for($service)->for($pool)->create([
        'service_date' => '2026-04-14',
        'status' => QueueStatus::Cancelled,
        'channel' => 'online_booking',
    ]);

    $report = app(LaporanBulananReportBuilder::class)->build(4, 2026);

    expect($report['ringkasan']['cancelled'])->toBe(2);
});

test('per layanan menyertakan id service dan menghitung skipped sebagai dibatalkan', function () {
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create();

    QueueTicket::factory()->for($service)->for($pool)->create([
        'service_date' => '2026-04-14',
        'status' => QueueStatus::Skipped,
        'channel' => 'online_booking',
    ]);

    QueueTicket::factory()->for($service)->for($pool)->create([
        'service_date' => '2026-04-14',
        'status' => QueueStatus::Completed,
        'channel' => 'online_booking',
        'completed_at' => '2026-04-14 10:00:00',
    ]);

    $report = app(LaporanBulananReportBuilder::class)->build(4, 2026);

    expect($report['per_layanan'])->toHaveCount(1);
    expect($report['per_layanan'][0])->toHaveKey('id');
    expect($report['per_layanan'][0]['id'])->toBe($service->id);
    expect($report['per_layanan'][0]['cancelled'])->toBe(1);
});

test('per channel menggunakan label terpusat', function () {
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create();

    QueueTicket::factory()->for($service)->for($pool)->create([
        'service_date' => '2026-04-14',
        'status' => QueueStatus::Completed,
        'channel' => 'online_booking',
        'completed_at' => '2026-04-14 10:00:00',
    ]);

    $report = app(LaporanBulananReportBuilder::class)->build(4, 2026);

    expect($report['per_channel'][0]['channel'])->toBe('Online Booking');
});

test('export action excel dan pdf memvalidasi input sebelum download', function () {
    $component = app(LaporanBulanan::class);
    $component->bulan = 13;
    $component->tahun = 2026;

    expect(fn () => $component->downloadExcel())
        ->toThrow(ValidationException::class);

    expect(fn () => $component->downloadPdf())
        ->toThrow(ValidationException::class);
});

test('action export excel berhasil dipanggil', function () {
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create();

    QueueTicket::factory()->for($service)->for($pool)->create([
        'service_date' => '2026-04-14',
        'status' => QueueStatus::Completed,
        'channel' => 'online_booking',
        'completed_at' => '2026-04-14 10:00:00',
    ]);

    Excel::fake();
    Excel::matchByRegex();

    $component = app(LaporanBulanan::class);
    $component->bulan = 4;
    $component->tahun = 2026;
    $component->downloadExcel();

    Excel::assertDownloaded('/\.xlsx$/');
});

test('action export pdf mengembalikan response pdf valid', function () {
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create();

    QueueTicket::factory()->for($service)->for($pool)->create([
        'service_date' => '2026-04-14',
        'status' => QueueStatus::Completed,
        'channel' => 'online_booking',
        'completed_at' => '2026-04-14 10:00:00',
    ]);

    $component = app(LaporanBulanan::class);
    $component->bulan = 4;
    $component->tahun = 2026;
    $response = $component->downloadPdf();

    expect($response)->toBeInstanceOf(StreamedResponse::class)
        ->and($response->headers->get('content-type'))->toContain('application/pdf');
});

test('action export pdf lewat livewire menghasilkan file download', function () {
    $user = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);

    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create();

    QueueTicket::factory()->for($service)->for($pool)->create([
        'service_date' => '2026-04-14',
        'status' => QueueStatus::Completed,
        'channel' => 'online_booking',
        'completed_at' => '2026-04-14 10:00:00',
    ]);

    Livewire::actingAs($user)
        ->test(LaporanBulanan::class)
        ->set('tahun', 2026)
        ->set('bulan', 4)
        ->call('downloadPdf')
        ->assertFileDownloaded('Laporan_Bulanan_April_2026.pdf', null, 'application/pdf');
});

test('halaman laporan menampilkan tombol export excel dan pdf', function () {
    $user = User::factory()->create([
        'role' => UserRole::Admin->value,
        'email_verified_at' => now(),
    ]);

    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create();

    QueueTicket::factory()->for($service)->for($pool)->create([
        'service_date' => now()->format('Y-m-d'),
        'status' => QueueStatus::Completed,
        'channel' => 'online_booking',
        'completed_at' => now()->format('Y-m-d H:i:s'),
    ]);

    $response = Livewire::actingAs($user)
        ->test(LaporanBulanan::class);

    expect($response->html())->toContain('Excel');
    expect($response->html())->toContain('PDF');
});

test('builder mengembalikan detail pengunjung dengan kolom no tanggal nama alamat layanan', function () {
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create(['name' => 'Pendaftaran']);
    $wilayah = Wilayah::factory()->create(['kode' => '640102', 'nama' => 'Penajam']);

    QueueTicket::factory()->for($service)->for($pool)->create([
        'service_date' => '2026-04-14',
        'status' => QueueStatus::Completed,
        'channel' => 'online_booking',
        'visitor_name' => 'Budi Santoso',
        'visitor_wilayah_kode' => '640102',
        'completed_at' => '2026-04-14 10:00:00',
    ]);

    $report = app(LaporanBulananReportBuilder::class)->build(4, 2026);

    expect($report)->toHaveKey('detail_pengunjung');
    expect($report['detail_pengunjung'])->toHaveCount(1);
    expect($report['detail_pengunjung'][0])->toMatchArray([
        'no' => 1,
        'tanggal' => '14/04/2026',
        'nama' => 'Budi Santoso',
        'alamat' => 'Penajam',
        'layanan' => 'Pendaftaran',
    ]);
});

test('export excel mengandung sheet detail pengunjung dengan header tanggal pendaftaran', function () {
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create();

    QueueTicket::factory()->count(2)->for($service)->for($pool)->create([
        'service_date' => '2026-04-14',
        'status' => QueueStatus::Completed,
        'channel' => 'online_booking',
        'completed_at' => '2026-04-14 10:00:00',
    ]);

    $report = app(LaporanBulananReportBuilder::class)->build(4, 2026);
    $export = new LaporanBulananExport($report);
    $sheets = $export->sheets();

    $titles = array_map(fn ($sheet) => $sheet->title(), $sheets);

    expect($titles)->toContain('Detail Pengunjung');

    $detailSheet = collect($sheets)->first(fn ($sheet) => $sheet->title() === 'Detail Pengunjung');
    expect($detailSheet->headings())->toBe([
        'No',
        'Tanggal Pendaftaran',
        'Nama Pengunjung',
        'Alamat/Wilayah',
        'Layanan yang diambil',
    ]);
});

test('export excel detail pengunjung menghindari formula injection', function () {
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create();

    QueueTicket::factory()->for($service)->for($pool)->create([
        'service_date' => '2026-04-14',
        'status' => QueueStatus::Completed,
        'channel' => 'online_booking',
        'visitor_name' => ' =HYPERLINK("http://evil","Budi")',
        'completed_at' => '2026-04-14 10:00:00',
    ]);

    $report = app(LaporanBulananReportBuilder::class)->build(4, 2026);
    $export = new LaporanBulananExport($report);
    $sheets = $export->sheets();

    $detailSheet = collect($sheets)->first(fn ($sheet) => $sheet->title() === 'Detail Pengunjung');
    $rows = $detailSheet->array();

    expect($rows[0][2])->toBe("' =HYPERLINK(\"http://evil\",\"Budi\")");
});

test('export excel per layanan menghindari formula injection', function () {
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create(['name' => '=SUM(A1:A10)']);

    QueueTicket::factory()->for($service)->for($pool)->create([
        'service_date' => '2026-04-14',
        'status' => QueueStatus::Completed,
        'channel' => 'online_booking',
        'completed_at' => '2026-04-14 10:00:00',
    ]);

    $report = app(LaporanBulananReportBuilder::class)->build(4, 2026);
    $export = new LaporanBulananExport($report);
    $sheets = $export->sheets();

    $perLayananSheet = collect($sheets)->first(fn ($sheet) => $sheet->title() === 'Per Layanan');
    $rows = $perLayananSheet->array();

    expect($rows[0][0])->toBe("'=SUM(A1:A10)");
});

test('nomor urut detail pengunjung bersifat sequential', function () {
    $pool = QueuePool::factory()->create();
    $service = Service::factory()->for($pool)->create();

    $ticket2 = QueueTicket::factory()->for($service)->for($pool)->create([
        'service_date' => '2026-04-14',
        'status' => QueueStatus::Completed,
        'channel' => 'online_booking',
        'visitor_name' => 'Andi Wijaya',
        'completed_at' => '2026-04-14 10:00:00',
    ]);

    $ticket1 = QueueTicket::factory()->for($service)->for($pool)->create([
        'service_date' => '2026-04-14',
        'status' => QueueStatus::Completed,
        'channel' => 'online_booking',
        'visitor_name' => 'Budi Santoso',
        'ticket_number' => 'A000',
        'completed_at' => '2026-04-14 11:00:00',
    ]);

    $report = app(LaporanBulananReportBuilder::class)->build(4, 2026);

    expect($report['detail_pengunjung'])->toHaveCount(2);
    expect($report['detail_pengunjung'][0])->toMatchArray(['no' => 1, 'nama' => 'Budi Santoso']);
    expect($report['detail_pengunjung'][1])->toMatchArray(['no' => 2, 'nama' => 'Andi Wijaya']);
});

test('builder mengembalikan judul bulan dalam bahasa indonesia', function () {
    $report = app(LaporanBulananReportBuilder::class)->build(4, 2026);

    expect($report)->toHaveKey('judul_bulan');
    expect($report['judul_bulan'])->toContain('April');
});
