<?php

namespace App\Actions\Queue;

use Illuminate\Support\Facades\Http;

class CheckPrinterConnectivity
{
    /**
     * Periksa apakah printer thermal dapat dijangkau dari server.
     *
     * @return array{ connected: bool, error: ?string }
     */
    public function handle(): array
    {
        if (! config('services.thermal_printer.enabled')) {
            return ['connected' => false, 'error' => 'Printer tidak diaktifkan di konfigurasi'];
        }

        $ip = config('services.thermal_printer.ip');
        $port = config('services.thermal_printer.port');
        $url = "http://{$ip}:{$port}/";

        try {
            Http::timeout(5)->get($url);

            return ['connected' => true, 'error' => null];
        } catch (\Throwable $e) {
            return ['connected' => false, 'error' => $e->getMessage()];
        }
    }
}
