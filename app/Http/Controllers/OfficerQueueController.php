<?php

namespace App\Http\Controllers;

use App\Actions\Queue\CallNextTicket;
use App\Actions\Queue\CancelTicket;
use App\Actions\Queue\CompleteTicket;
use App\Actions\Queue\RecallTicket;
use App\Actions\Queue\SkipTicket;
use App\Http\Requests\QueueTicketActionRequest;
use App\Models\Counter;
use App\Models\QueueTicket;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class OfficerQueueController extends Controller
{
    public function show(Counter $counter): View
    {
        $user = request()->user();

        abort_if(! $user, 403);

        $allowedPoolIds = $user->services()
            ->pluck('queue_pool_id')
            ->filter()
            ->values();

        if ($allowedPoolIds->isNotEmpty()) {
            abort_if(! $allowedPoolIds->contains($counter->queue_pool_id), 403);
        }

        $counter->loadMissing('queuePool');

        return view('pages.officer.counter', [
            'counter' => $counter,
        ]);
    }

    public function callNext(Counter $counter, CallNextTicket $callNextTicket): Response
    {
        $this->ensureOfficerCanAccessPool($counter);

        $ticket = $callNextTicket->handle($counter, request()->user()?->id);

        if (! $ticket) {
            return response('Tidak ada antrean', 200);
        }

        return response("Panggil Berikutnya: {$ticket->ticket_number}", 200);
    }

    public function recall(Counter $counter, QueueTicketActionRequest $request, RecallTicket $recallTicket): Response
    {
        $this->ensureOfficerCanAccessPool($counter);

        $ticket = QueueTicket::query()->findOrFail($request->integer('ticket_id'));
        $this->ensureTicketPoolMatchesCounterPool($ticket, $counter);

        $updated = $recallTicket->handle($ticket, $counter, $request->user()?->id);

        return response("Panggil Ulang: {$updated->ticket_number}", 200);
    }

    public function skip(Counter $counter, QueueTicketActionRequest $request, SkipTicket $skipTicket): Response
    {
        $this->ensureOfficerCanAccessPool($counter);

        $ticket = QueueTicket::query()->findOrFail($request->integer('ticket_id'));
        $this->ensureTicketPoolMatchesCounterPool($ticket, $counter);

        $updated = $skipTicket->handle($ticket, $counter, $request->user()?->id);

        return response("Lewati: {$updated->ticket_number}", 200);
    }

    public function complete(Counter $counter, QueueTicketActionRequest $request, CompleteTicket $completeTicket): Response
    {
        $this->ensureOfficerCanAccessPool($counter);

        $ticket = QueueTicket::query()->findOrFail($request->integer('ticket_id'));
        $this->ensureTicketPoolMatchesCounterPool($ticket, $counter);

        $updated = $completeTicket->handle($ticket, $counter, $request->user()?->id);

        return response("Selesai: {$updated->ticket_number}", 200);
    }

    public function cancel(Counter $counter, QueueTicketActionRequest $request, CancelTicket $cancelTicket): Response
    {
        $this->ensureOfficerCanAccessPool($counter);

        $ticket = QueueTicket::query()->findOrFail($request->integer('ticket_id'));
        $this->ensureTicketPoolMatchesCounterPool($ticket, $counter);

        $updated = $cancelTicket->handle($ticket, $counter, $request->user()?->id);

        return response("Batal: {$updated->ticket_number}", 200);
    }

    private function ensureOfficerCanAccessPool(Counter $counter): void
    {
        $user = request()->user();

        abort_if(! $user, 403);

        $allowedPoolIds = $user->services()
            ->pluck('queue_pool_id')
            ->filter()
            ->values();

        if ($allowedPoolIds->isNotEmpty()) {
            abort_if(! $allowedPoolIds->contains($counter->queue_pool_id), 403);
        }
    }

    private function ensureTicketPoolMatchesCounterPool(QueueTicket $queueTicket, Counter $counter): void
    {
        abort_if($queueTicket->queue_pool_id !== $counter->queue_pool_id, 403);
    }
}
