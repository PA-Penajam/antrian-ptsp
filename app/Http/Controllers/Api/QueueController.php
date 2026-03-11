<?php

namespace App\Http\Controllers\Api;

use App\Actions\Queue\CreateQueueTicket;
use App\Http\Controllers\Controller;
use App\Http\Resources\QueueTicketResource;
use App\Models\QueueTicket;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    /**
     * Store a newly created queue ticket (booking).
     */
    public function booking(Request $request, CreateQueueTicket $createQueueTicket)
    {
        $validated = $request->validate([
            'service_id' => 'required|integer|exists:services,id',
            'service_date' => 'required|date',
            'visitor_name' => 'required|string|max:255',
            'visitor_identifier' => 'nullable|string|max:255',
            'visitor_phone' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

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

        $ticket->load(['service', 'counter']);

        return new QueueTicketResource($ticket);
    }

    /**
     * Lookup a ticket by ticket_number and service_date.
     */
    public function lookup(Request $request)
    {
        $validated = $request->validate([
            'ticket_number' => 'required|string',
            'service_date' => 'required|date',
        ]);

        $ticket = QueueTicket::query()
            ->with(['service', 'counter'])
            ->where('ticket_number', $validated['ticket_number'])
            ->whereDate('service_date', $validated['service_date'])
            ->firstOrFail();

        return new QueueTicketResource($ticket);
    }

    /**
     * Get a specific ticket details by ticket number.
     */
    public function showTicket(string $identifier)
    {
        $ticket = QueueTicket::query()
            ->with(['service', 'counter'])
            ->where(function ($query) use ($identifier) {
                if (is_numeric($identifier)) {
                    $query->where('id', $identifier);
                }
                $query->orWhere('ticket_number', $identifier);
            })
            ->latest('id')
            ->firstOrFail();

        return new QueueTicketResource($ticket);
    }
}
