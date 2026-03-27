<?php

namespace App\Actions\Queue;

use App\Models\QueueTicket;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PrintTicketToEposPrinter
{
    public function handle(QueueTicket $ticket): bool
    {
        if (! config('services.thermal_printer.enabled')) {
            return false;
        }

        $ip = config('services.thermal_printer.ip');
        $port = config('services.thermal_printer.port');
        $deviceId = config('services.thermal_printer.device_id');
        $url = "http://{$ip}:{$port}/cgi-bin/epos/service.cgi?devid={$deviceId}&timeout=10000";

        try {
            $response = Http::timeout(12)
                ->withHeaders(['SOAPAction' => '""'])
                ->withBody($this->buildXml($ticket), 'text/xml; charset=utf-8')
                ->post($url);

            if (! $response->successful()) {
                Log::warning('[Printer] HTTP error', ['status' => $response->status()]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('[Printer] Unreachable', ['error' => $e->getMessage()]);

            return false;
        }
    }

    private function buildXml(QueueTicket $ticket): string
    {
        $institution = htmlspecialchars((string) config('institution.name'), ENT_XML1, 'UTF-8');
        $ticketNumber = htmlspecialchars($ticket->ticket_number, ENT_XML1, 'UTF-8');
        $serviceName = htmlspecialchars($ticket->service->name, ENT_XML1, 'UTF-8');
        $visitorName = htmlspecialchars($ticket->visitor_name, ENT_XML1, 'UTF-8');
        $serviceDate = $ticket->service_date->format('d/m/Y');

        return <<<XML
            <?xml version="1.0" encoding="utf-8"?>
            <s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
              <s:Body>
                <epos-print xmlns="http://www.epson-pos.com/schemas/2011/03/epos-print">
                  <text align="center" width="1" height="1" bold="true">{$institution}&#10;</text>
                  <text align="center">Sistem Pelayanan Terpadu Satu Pintu&#10;</text>
                  <text>------------------------------------------&#10;</text>
                  <feed line="1"/>
                  <text align="center" width="3" height="3" bold="true">{$ticketNumber}&#10;</text>
                  <feed line="1"/>
                  <text>------------------------------------------&#10;</text>
                  <text align="left">Layanan : {$serviceName}&#10;</text>
                  <text>Nama    : {$visitorName}&#10;</text>
                  <text>Tanggal : {$serviceDate}&#10;</text>
                  <feed line="3"/>
                  <cut type="feed"/>
                </epos-print>
              </s:Body>
            </s:Envelope>
            XML;
    }
}
