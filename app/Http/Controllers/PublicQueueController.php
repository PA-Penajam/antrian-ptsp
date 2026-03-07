<?php

namespace App\Http\Controllers;

use App\Actions\Queue\CreateQueueTicket;
use App\Enums\QueueStatus;
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
        $queuePosition = 0;
        if (! empty($validated['ticket_number']) && ! empty($validated['service_date'])) {
            $ticket = QueueTicket::query()
                ->with(['service', 'counter', 'queuePool'])
                ->where('ticket_number', $validated['ticket_number'])
                ->whereDate('service_date', $validated['service_date'])
                ->first();

            if ($ticket && $ticket->status === QueueStatus::Waiting && $ticket->queuePool) {
                $queuePosition = QueueTicket::query()
                    ->where('queue_pool_id', $ticket->queue_pool_id)
                    ->whereDate('service_date', $ticket->service_date)
                    ->where('status', QueueStatus::Waiting)
                    ->where('sequence_number', '<', $ticket->sequence_number)
                    ->count() + 1;
            }
        }

        return view('pages.public.antrian.lookup', [
            'ticket' => $ticket,
            'queuePosition' => $queuePosition,
        ]);
    }

    public function confirmation(QueueTicket $ticket): View
    {
        $ticket->load(['service', 'queuePool', 'counter']);

        $queuePosition = 0;
        if ($ticket->status === QueueStatus::Waiting && $ticket->queuePool) {
            $queuePosition = QueueTicket::query()
                ->where('queue_pool_id', $ticket->queue_pool_id)
                ->whereDate('service_date', $ticket->service_date)
                ->where('status', QueueStatus::Waiting)
                ->where('sequence_number', '<', $ticket->sequence_number)
                ->count() + 1;
        }

        return view('pages.public.antrian.confirmation', [
            'ticket' => $ticket,
            'queuePosition' => $queuePosition,
        ]);
    }
}
