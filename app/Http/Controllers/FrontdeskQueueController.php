<?php

namespace App\Http\Controllers;

use App\Actions\Queue\CheckInQueueTicket;
use App\Actions\Queue\CreateQueueTicket;
use App\Http\Requests\CheckInQueueTicketRequest;
use App\Http\Requests\StoreFrontdeskQueueTicketRequest;
use App\Models\QueueTicket;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;

class FrontdeskQueueController extends Controller
{
    public function index(): View
    {
        return view('pages.frontdesk.antrian', [
            'ticket' => null,
            'checkedInTicket' => null,
            'services' => Service::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function store(StoreFrontdeskQueueTicketRequest $request, CreateQueueTicket $createQueueTicket): View
    {
        $validated = $request->validated();

        $ticket = $createQueueTicket->handle([
            'service_id' => (int) $validated['service_id'],
            'channel' => $validated['channel'],
            'service_date' => CarbonImmutable::parse($validated['service_date']),
            'visitor_name' => $validated['visitor_name'],
            'visitor_identifier' => $validated['visitor_identifier'] ?? null,
            'visitor_phone' => $validated['visitor_phone'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => $request->user()?->id,
        ]);

        return view('pages.frontdesk.antrian', [
            'ticket' => $ticket,
            'checkedInTicket' => null,
            'services' => Service::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function checkIn(CheckInQueueTicketRequest $request, CheckInQueueTicket $checkInQueueTicket): View
    {
        $validated = $request->validated();
        $queueTicket = QueueTicket::query()->findOrFail($validated['ticket_id']);
        $checkedInTicket = $checkInQueueTicket->handle($queueTicket, $request->user()?->id);

        return view('pages.frontdesk.antrian', [
            'ticket' => null,
            'checkedInTicket' => $checkedInTicket,
            'services' => Service::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }
}
