<?php

use App\Actions\Queue\PrintTicketToEposPrinter;
use App\Models\QueuePool;
use App\Models\QueueTicket;
use App\Models\Service;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'services.thermal_printer.enabled' => true,
        'services.thermal_printer.ip' => '192.168.10.27',
        'services.thermal_printer.port' => 8008,
        'services.thermal_printer.device_id' => 'local_printer',
        'institution.name' => 'Pengadilan Agama Test',
    ]);
});

function makePrintableTicket(): QueueTicket
{
    $pool = QueuePool::factory()->create(['letter_code' => 'A']);
    $service = Service::factory()->create(['name' => 'Pemberkasan', 'queue_pool_id' => $pool->id]);
    $ticket = QueueTicket::factory()->create([
        'service_id' => $service->id,
        'ticket_number' => 'A1',
        'visitor_name' => 'Budi Santoso',
        'service_date' => '2026-03-27',
    ]);
    $ticket->load('service');

    return $ticket;
}

it('returns false immediately when printer is disabled', function () {
    config(['services.thermal_printer.enabled' => false]);
    Http::fake();

    $result = app(PrintTicketToEposPrinter::class)->handle(makePrintableTicket());

    expect($result)->toBeFalse();
    Http::assertNothingSent();
});

it('returns true when printer responds with 200', function () {
    Http::fake(['*' => Http::response('', 200)]);

    $result = app(PrintTicketToEposPrinter::class)->handle(makePrintableTicket());

    expect($result)->toBeTrue();
});

it('sends XML payload with correct ticket data and URL params', function () {
    Http::fake(['*' => Http::response('', 200)]);

    app(PrintTicketToEposPrinter::class)->handle(makePrintableTicket());

    Http::assertSent(function (Request $request) {
        $body = $request->body();
        $url = $request->url();

        return str_contains($body, 'A1')
            && str_contains($body, 'Pemberkasan')
            && str_contains($body, 'Budi Santoso')
            && str_contains($body, '27/03/2026')
            && str_contains($body, 'Pengadilan Agama Test')
            && str_contains($url, 'devid=local_printer')
            && str_contains($url, 'timeout=10000')
            && str_contains($url, '192.168.10.27:8008');
    });
});

it('returns false when printer returns non-2xx', function () {
    Http::fake(['*' => Http::response('Service Unavailable', 503)]);

    $result = app(PrintTicketToEposPrinter::class)->handle(makePrintableTicket());

    expect($result)->toBeFalse();
});

it('returns false when printer is unreachable', function () {
    Http::fake(function () {
        throw new \Illuminate\Http\Client\ConnectionException('Connection refused');
    });

    $result = app(PrintTicketToEposPrinter::class)->handle(makePrintableTicket());

    expect($result)->toBeFalse();
});
