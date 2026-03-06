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
use Illuminate\Http\Response;

class OfficerQueueController extends Controller
{
    public function show(Counter $counter): Response
    {
        return response("Loket {$counter->id}", 200);
    }

    public function callNext(Counter $counter, CallNextTicket $callNextTicket): Response
    {
        $ticket = $callNextTicket->handle($counter, request()->user()?->id);

        if (! $ticket) {
            return response('Tidak ada antrean', 200);
        }

        return response("Panggil Berikutnya: {$ticket->ticket_number}", 200);
    }

    public function recall(Counter $counter, QueueTicketActionRequest $request, RecallTicket $recallTicket): Response
    {
        $ticket = QueueTicket::query()->findOrFail($request->integer('ticket_id'));
        $this->ensureTicketPoolMatchesCounterPool($ticket, $counter);

        $updated = $recallTicket->handle($ticket, $counter, $request->user()?->id);

        return response("Panggil Ulang: {$updated->ticket_number}", 200);
    }

    public function skip(Counter $counter, QueueTicketActionRequest $request, SkipTicket $skipTicket): Response
    {
        $ticket = QueueTicket::query()->findOrFail($request->integer('ticket_id'));
        $this->ensureTicketPoolMatchesCounterPool($ticket, $counter);

        $updated = $skipTicket->handle($ticket, $counter, $request->user()?->id);

        return response("Lewati: {$updated->ticket_number}", 200);
    }

    public function complete(Counter $counter, QueueTicketActionRequest $request, CompleteTicket $completeTicket): Response
    {
        $ticket = QueueTicket::query()->findOrFail($request->integer('ticket_id'));
        $this->ensureTicketPoolMatchesCounterPool($ticket, $counter);

        $updated = $completeTicket->handle($ticket, $counter, $request->user()?->id);

        return response("Selesai: {$updated->ticket_number}", 200);
    }

    public function cancel(Counter $counter, QueueTicketActionRequest $request, CancelTicket $cancelTicket): Response
    {
        $ticket = QueueTicket::query()->findOrFail($request->integer('ticket_id'));
        $this->ensureTicketPoolMatchesCounterPool($ticket, $counter);

        $updated = $cancelTicket->handle($ticket, $counter, $request->user()?->id);

        return response("Batal: {$updated->ticket_number}", 200);
    }

    private function ensureTicketPoolMatchesCounterPool(QueueTicket $queueTicket, Counter $counter): void
    {
        abort_if($queueTicket->queue_pool_id !== $counter->queue_pool_id, 403);
    }
}
