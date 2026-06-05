<?php

use App\Enums\QueueStatus;
use App\Models\QueueTicket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;

use function Pest\Laravel\getJson;
use function Pest\Laravel\withHeaders;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->withoutMiddleware(ThrottleRequests::class));

beforeEach(function () {
    // Set shared secret untuk endpoint (dibaca controller via env()).
    putenv('SURVEY_DASHBOARD_API_KEY=secret-test-key');
    $_ENV['SURVEY_DASHBOARD_API_KEY'] = 'secret-test-key';
    $_SERVER['SURVEY_DASHBOARD_API_KEY'] = 'secret-test-key';
});

afterEach(function () {
    putenv('SURVEY_DASHBOARD_API_KEY');
    unset($_ENV['SURVEY_DASHBOARD_API_KEY'], $_SERVER['SURVEY_DASHBOARD_API_KEY']);
});

test('menolak permintaan tanpa X-Api-Key', function () {
    getJson('/api/served-counts?start=2026-06-01&end=2026-06-05')
        ->assertStatus(401);
});

test('menolak X-Api-Key yang salah', function () {
    withHeaders(['X-Api-Key' => 'kunci-salah'])
        ->getJson('/api/served-counts?start=2026-06-01&end=2026-06-05')
        ->assertStatus(401);
});

test('mengembalikan jumlah tiket completed per tanggal', function () {
    QueueTicket::factory()->count(3)->create([
        'status' => QueueStatus::Completed,
        'service_date' => '2026-06-02',
    ]);
    QueueTicket::factory()->count(2)->create([
        'status' => QueueStatus::Completed,
        'service_date' => '2026-06-03',
    ]);
    // Tiket non-completed harus diabaikan.
    QueueTicket::factory()->count(5)->create([
        'status' => QueueStatus::Waiting,
        'service_date' => '2026-06-02',
    ]);

    withHeaders(['X-Api-Key' => 'secret-test-key'])
        ->getJson('/api/served-counts?start=2026-06-01&end=2026-06-05')
        ->assertOk()
        ->assertJsonPath('start', '2026-06-01')
        ->assertJsonPath('end', '2026-06-05')
        ->assertJsonFragment(['date' => '2026-06-02', 'served' => 3])
        ->assertJsonFragment(['date' => '2026-06-03', 'served' => 2]);
});

test('menolak rentang tanggal terbalik', function () {
    withHeaders(['X-Api-Key' => 'secret-test-key'])
        ->getJson('/api/served-counts?start=2026-06-10&end=2026-06-01')
        ->assertStatus(422);
});

test('menerima rentang tepat 92 hari', function () {
    withHeaders(['X-Api-Key' => 'secret-test-key'])
        ->getJson('/api/served-counts?start=2026-01-01&end=2026-04-03')
        ->assertOk();
});

test('menolak rentang lebih dari 92 hari', function () {
    withHeaders(['X-Api-Key' => 'secret-test-key'])
        ->getJson('/api/served-counts?start=2026-01-01&end=2026-04-04')
        ->assertStatus(422);
});

test('mengembalikan data kosong untuk rentang tanpa tiket', function () {
    withHeaders(['X-Api-Key' => 'secret-test-key'])
        ->getJson('/api/served-counts?start=2026-07-01&end=2026-07-05')
        ->assertOk()
        ->assertJsonPath('data', []);
});
