<?php

namespace App\Http\Controllers\Api;

use App\Actions\Queue\CreateQueueTicket;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LookupTicketRequest;
use App\Http\Requests\Api\StoreBookingRequest;
use App\Http\Resources\QueueTicketResource;
use App\Models\QueueTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicQueueController extends Controller
{
    public function booking(StoreBookingRequest $request, CreateQueueTicket $action): JsonResponse
    {
        $validated = $request->validated();

        $ticket = $action->handle([
            'service_id' => (int) $validated['service_id'],
            'channel' => 'online_booking',
            'service_date' => $validated['service_date'],
            'visitor_name' => $validated['visitor_name'],
            'visitor_identifier' => $validated['visitor_identifier'] ?? null,
            'visitor_phone' => $validated['visitor_phone'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => null,
        ]);

        $ticket->load(['service', 'queuePool']);

        return QueueTicketResource::make($ticket)->response()->setStatusCode(201);
    }

    public function lookup(LookupTicketRequest $request): JsonResponse
    {
        $ticket = $this->findTicket($request->ticket_number, $request->service_date);

        if (! $ticket) {
            return response()->json(['message' => 'Tiket tidak ditemukan'], 404);
        }

        return QueueTicketResource::make($ticket)->response();
    }

    public function show(Request $request, string $ticketNumber): JsonResponse
    {
        $request->validate([
            'service_date' => ['required', 'date'],
        ]);

        $ticket = $this->findTicket($ticketNumber, $request->service_date);

        if (! $ticket) {
            return response()->json(['message' => 'Tiket tidak ditemukan'], 404);
        }

        return QueueTicketResource::make($ticket)->response();
    }

    private function findTicket(string $ticketNumber, string $serviceDate): ?QueueTicket
    {
        return QueueTicket::query()
            ->with(['service', 'counter', 'queuePool'])
            ->where('ticket_number', $ticketNumber)
            ->whereDate('service_date', $serviceDate)
            ->first();
    }
}
