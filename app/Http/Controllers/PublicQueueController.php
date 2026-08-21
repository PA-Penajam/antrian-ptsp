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
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;

class PublicQueueController extends Controller
{
    public function index(): View
    {
        $today = CarbonImmutable::today();
        $services = Service::active()->get();

        $todayStats = [
            'total' => QueueTicket::query()->whereDate('service_date', $today)->count(),
            'waiting' => QueueTicket::query()->whereDate('service_date', $today)->where('status', QueueStatus::Waiting)->count(),
            'completed' => QueueTicket::query()->whereDate('service_date', $today)->where('status', QueueStatus::Completed)->count(),
        ];

        $activeCallingTickets = QueueTicket::query()
            ->with(['counter', 'service'])
            ->whereDate('service_date', $today)
            ->where('status', QueueStatus::Called)
            ->orderByDesc('called_at')
            ->take(4)
            ->get();

        return view('welcome', [
            'services' => $services,
            'todayStats' => $todayStats,
            'activeCallingTickets' => $activeCallingTickets,
        ]);
    }

    public function booking(): View
    {
        $bookableServices = Service::active()
            ->where('booking_enabled', true)
            ->get();

        return view('pages.public.antrian.booking', [
            'bookableServices' => $bookableServices,
            'ticket' => null,
        ]);
    }

    public function storeBooking(StorePublicQueueBookingRequest $request, CreateQueueTicket $createQueueTicket): RedirectResponse
    {
        $validated = $request->validated();

        $ticket = $createQueueTicket->handle([
            'service_id' => (int) $validated['service_id'],
            'channel' => 'online_booking',
            'service_date' => CarbonImmutable::parse($validated['service_date']),
            'visitor_name' => $validated['visitor_name'],
            'visitor_identifier' => $validated['visitor_identifier'] ?? null,
            'visitor_phone' => $validated['visitor_phone'] ?? null,
            'visit_purpose' => $validated['visit_purpose'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => null,
        ]);

        return redirect()->to(URL::signedRoute('queue.confirmation', $ticket));
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
        // Explicitly reload with relationships to ensure all attributes are loaded
        $loadedTicket = QueueTicket::query()
            ->with(['service', 'queuePool', 'counter'])
            ->findOrFail($ticket->id);

        $queuePosition = 0;
        if ($loadedTicket->status === QueueStatus::Waiting && $loadedTicket->queuePool) {
            $queuePosition = QueueTicket::query()
                ->where('queue_pool_id', $loadedTicket->queue_pool_id)
                ->whereDate('service_date', $loadedTicket->service_date)
                ->where('status', QueueStatus::Waiting)
                ->where('sequence_number', '<', $loadedTicket->sequence_number)
                ->count() + 1;
        }

        return view('pages.public.antrian.confirmation', [
            'ticket' => $loadedTicket,
            'queuePosition' => $queuePosition,
        ]);
    }
}
