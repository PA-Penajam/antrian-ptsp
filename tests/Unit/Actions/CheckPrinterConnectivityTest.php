<?php

use App\Actions\Queue\CheckPrinterConnectivity;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'services.thermal_printer.enabled' => true,
        'services.thermal_printer.ip' => '192.168.10.27',
        'services.thermal_printer.port' => 8008,
    ]);
});

it('returns connected false immediately when printer is disabled in config', function () {
    config(['services.thermal_printer.enabled' => false]);
    Http::fake();

    $result = app(CheckPrinterConnectivity::class)->handle();

    expect($result['connected'])->toBeFalse()
        ->and($result['error'])->toContain('tidak diaktifkan');
    Http::assertNothingSent();
});

it('returns connected true when printer responds with 200', function () {
    Http::fake(['*' => Http::response('', 200)]);

    $result = app(CheckPrinterConnectivity::class)->handle();

    expect($result['connected'])->toBeTrue()
        ->and($result['error'])->toBeNull();
});

it('returns connected true even when printer responds with 404', function () {
    Http::fake(['*' => Http::response('Not Found', 404)]);

    $result = app(CheckPrinterConnectivity::class)->handle();

    expect($result['connected'])->toBeTrue()
        ->and($result['error'])->toBeNull();
});

it('returns connected false with error message when connection times out', function () {
    Http::fake(function () {
        throw new \Illuminate\Http\Client\ConnectionException('cURL error 28: Operation timed out');
    });

    $result = app(CheckPrinterConnectivity::class)->handle();

    expect($result['connected'])->toBeFalse()
        ->and($result['error'])->toContain('timed out');
});

it('returns connected false with error message when connection is refused', function () {
    Http::fake(function () {
        throw new \Illuminate\Http\Client\ConnectionException('cURL error 7: Failed to connect');
    });

    $result = app(CheckPrinterConnectivity::class)->handle();

    expect($result['connected'])->toBeFalse()
        ->and($result['error'])->not->toBeNull();
});

it('sends get request to root url of printer', function () {
    Http::fake(['*' => Http::response('', 200)]);

    app(CheckPrinterConnectivity::class)->handle();

    Http::assertSent(function (Request $request) {
        return $request->url() === 'http://192.168.10.27:8008/'
            && $request->method() === 'GET';
    });
});
