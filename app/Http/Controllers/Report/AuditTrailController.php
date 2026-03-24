<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\QueueActivity;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditTrailController extends Controller
{
    public function index(Request $request): View
    {
        $date = $request->query('date', now()->toDateString());
        $search = $request->query('search', '');

        $activities = QueueActivity::query()
            ->with(['user', 'queueTicket.service', 'counter'])
            ->whereDate('created_at', $date)
            ->when($search, function ($query, $search) {
                $query->whereHas('queueTicket', function ($q) use ($search) {
                    $q->where('ticket_number', 'like', "%{$search}%");
                })->orWhereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhereHas('counter', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return view('pages.laporan.audit.index', [
            'activities' => $activities,
            'date' => $date,
            'search' => $search,
        ]);
    }
}
