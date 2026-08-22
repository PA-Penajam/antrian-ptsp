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
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

class FrontdeskQueueController extends Controller
{
    public function index(): View
    {
        $createdTicket = null;
        $checkedInTicket = null;

        $createdTicketId = session('created_ticket_id');
        if (is_numeric($createdTicketId)) {
            $createdTicket = QueueTicket::query()->find((int) $createdTicketId);
        }

        $checkedInTicketId = session('checked_in_ticket_id');
        if (is_numeric($checkedInTicketId)) {
            $checkedInTicket = QueueTicket::query()->find((int) $checkedInTicketId);
        }

        $services = Service::active()->get();
        $umumService = $services->firstWhere('code', 'UMUM');

        return view('pages.frontdesk.antrian', [
            'ticket' => $createdTicket,
            'checkedInTicket' => $checkedInTicket,
            'services' => $services,
            'umumServiceId' => $umumService?->id,
        ]);
    }

    public function store(StoreFrontdeskQueueTicketRequest $request, CreateQueueTicket $createQueueTicket): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $ticket = $createQueueTicket->handle([
                'service_id' => (int) $validated['service_id'],
                'channel' => $validated['channel'],
                'service_date' => CarbonImmutable::parse($validated['service_date']),
                'visitor_name' => $validated['visitor_name'],
                'visitor_identifier' => $validated['visitor_identifier'] ?? null,
                'visitor_phone' => $validated['visitor_phone'] ?? null,
                'visit_purpose' => $validated['visit_purpose'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => $request->user()?->id,
            ]);
        } catch (QueryException $e) {
            Log::warning('[Frontdesk] Gagal membuat tiket (constraint)', ['error' => $e->getMessage(), 'code' => $e->getCode()]);

            return back()->withInput()
                ->with('error', 'Gagal membuat tiket. Kuota mungkin penuh atau data bertabrakan. Coba lagi.');
        } catch (Throwable $e) {
            Log::error('[Frontdesk] Gagal membuat tiket', ['error' => $e->getMessage(), 'user_id' => $request->user()?->id]);

            return back()->withInput()
                ->with('error', 'Gagal membuat tiket. Periksa koneksi dan coba lagi. Jika berlanjut, hubungi admin.');
        }

        return redirect()
            ->route('frontdesk.queue.index')
            ->with('created_ticket_id', $ticket->id)
            ->with('status', 'Tiket berhasil dibuat.');
    }

    public function checkIn(CheckInQueueTicketRequest $request, CheckInQueueTicket $checkInQueueTicket): RedirectResponse
    {
        $validated = $request->validated();
        $queueTicket = QueueTicket::query()
            ->where('ticket_number', $validated['ticket_number'])
            ->firstOrFail();

        try {
            $checkedInTicket = $checkInQueueTicket->handle($queueTicket, $request->user()?->id);
        } catch (InvalidArgumentException) {
            return back()
                ->withInput()
                ->withErrors([
                    'ticket_number' => 'Tiket ini tidak dapat check-in karena statusnya bukan terdaftar (online).',
                ]);
        } catch (Throwable $e) {
            Log::error('[Frontdesk] Gagal check-in tiket', ['error' => $e->getMessage(), 'ticket_number' => $validated['ticket_number']]);

            return back()->withInput()
                ->with('error', 'Gagal memproses check-in. Periksa koneksi dan coba lagi.');
        }

        return redirect()
            ->route('frontdesk.queue.index')
            ->with('checked_in_ticket_id', $checkedInTicket->id)
            ->with('status', 'Check-in tiket berhasil diproses.');
    }
}
