<?php

namespace App\Http\Controllers;

use App\Actions\Queue\CreateQueueTicket;
use App\Http\Requests\LookupQueueTicketRequest;
use App\Http\Requests\StorePublicQueueBookingRequest;
use App\Models\QueueTicket;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;

class PublicQueueController extends Controller
{
    public function booking(): View
    {
        $services = Service::query()
            ->where('is_active', true)
            ->where('booking_enabled', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('pages.public.antrian.booking', [
            'services' => $services,
            'ticket' => null,
        ]);
    }

    public function storeBooking(StorePublicQueueBookingRequest $request, CreateQueueTicket $createQueueTicket): View
    {
        $validated = $request->validated();

        $ticket = $createQueueTicket->handle([
            'service_id' => (int) $validated['service_id'],
            'channel' => 'online_booking',
            'service_date' => CarbonImmutable::parse($validated['service_date']),
            'visitor_name' => $validated['visitor_name'],
            'visitor_identifier' => $validated['visitor_identifier'] ?? null,
            'visitor_phone' => $validated['visitor_phone'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => null,
        ]);

        $services = Service::query()
            ->where('is_active', true)
            ->where('booking_enabled', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('pages.public.antrian.booking', [
            'services' => $services,
            'ticket' => $ticket,
        ]);
    }

    public function lookup(LookupQueueTicketRequest $request): View
    {
        $validated = $request->validated();

        $ticket = null;
        if (! empty($validated['ticket_number']) && ! empty($validated['service_date'])) {
            $ticket = QueueTicket::query()
                ->where('ticket_number', $validated['ticket_number'])
                ->whereDate('service_date', $validated['service_date'])
                ->first();
        }

        return view('pages.public.antrian.lookup', [
            'ticket' => $ticket,
        ]);
    }
}
