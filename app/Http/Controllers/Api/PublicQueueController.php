<?php

namespace App\Http\Controllers\Api;

use App\Actions\Queue\CreateQueueTicket;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreBookingRequest;
use App\Http\Resources\QueueTicketResource;
use Illuminate\Http\JsonResponse;

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
}
