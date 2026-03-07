<?php

namespace App\Livewire\Dashboard;

use App\Enums\QueueStatus;
use App\Models\QueueTicket;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class AdminDashboard extends Component
{
    public ?string $startDate = null;

    public ?string $endDate = null;

    public function mount(): void
    {
        $this->startDate = today()->toDateString();
        $this->endDate = today()->toDateString();
    }

    #[Computed]
    public function todayTotal(): int
    {
        return QueueTicket::query()
            ->whereBetween('service_date', [$this->startDate, $this->endDate])
            ->count();
    }

    #[Computed]
    public function todayServed(): int
    {
        return QueueTicket::query()
            ->whereBetween('service_date', [$this->startDate, $this->endDate])
            ->where('status', QueueStatus::Completed)
            ->count();
    }

    #[Computed]
    public function todayWaiting(): int
    {
        return QueueTicket::query()
            ->whereBetween('service_date', [$this->startDate, $this->endDate])
            ->whereIn('status', [
                QueueStatus::Booked,
                QueueStatus::Waiting,
                QueueStatus::Called,
            ])
            ->count();
    }

    #[Computed]
    public function todayAvgWaitMinutes(): float
    {
        $tickets = QueueTicket::query()
            ->whereBetween('service_date', [$this->startDate, $this->endDate])
            ->where('status', QueueStatus::Completed)
            ->whereNotNull('called_at')
            ->whereNotNull('completed_at')
            ->get(['called_at', 'completed_at']);

        if ($tickets->isEmpty()) {
            return 0.0;
        }

        $totalMinutes = $tickets->sum(fn ($ticket) => $ticket->called_at->diffInMinutes($ticket->completed_at));

        return round($totalMinutes / $tickets->count(), 1);
    }

    #[Computed]
    public function bookingSuccess(): int
    {
        return QueueTicket::query()
            ->whereBetween('service_date', [$this->startDate, $this->endDate])
            ->where('channel', 'online_booking')
            ->where('status', '!=', QueueStatus::Cancelled)
            ->count();
    }

    #[Computed]
    public function bookingFailed(): int
    {
        return QueueTicket::query()
            ->whereBetween('service_date', [$this->startDate, $this->endDate])
            ->where('channel', 'online_booking')
            ->where('status', QueueStatus::Cancelled)
            ->count();
    }

    #[Computed]
    public function byService(): array
    {
        return QueueTicket::query()
            ->whereBetween('service_date', [$this->startDate, $this->endDate])
            ->join('services', 'queue_tickets.service_id', '=', 'services.id')
            ->select('services.name', DB::raw('COUNT(*) as count'))
            ->groupBy('services.name')
            ->orderByDesc('count')
            ->pluck('count', 'services.name')
            ->toArray();
    }

    #[Computed]
    public function byCounter(): array
    {
        return QueueTicket::query()
            ->whereBetween('service_date', [$this->startDate, $this->endDate])
            ->whereNotNull('counter_id')
            ->join('counters', 'queue_tickets.counter_id', '=', 'counters.id')
            ->select('counters.name', DB::raw('COUNT(*) as count'))
            ->groupBy('counters.name')
            ->orderByDesc('count')
            ->pluck('count', 'counters.name')
            ->toArray();
    }

    #[Computed]
    public function byChannel(): array
    {
        $counts = QueueTicket::query()
            ->whereBetween('service_date', [$this->startDate, $this->endDate])
            ->select('channel', DB::raw('COUNT(*) as count'))
            ->groupBy('channel')
            ->pluck('count', 'channel')
            ->toArray();

        return [
            'online_booking' => $counts['online_booking'] ?? 0,
            'assisted_same_day' => $counts['assisted_same_day'] ?? 0,
            'walk_in_kiosk' => $counts['walk_in_kiosk'] ?? 0,
        ];
    }

    #[Computed]
    public function trendData(): array
    {
        $days = 7;
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $count = QueueTicket::query()
                ->whereDate('service_date', $date)
                ->count();

            $data[] = [
                'date' => $date->format('Y-m-d'),
                'total' => $count,
            ];
        }

        return $data;
    }

    #[Computed]
    public function recentActivities(): \Illuminate\Database\Eloquent\Collection
    {
        return \App\Models\QueueActivity::query()
            ->with(['queueTicket.service', 'user', 'counter'])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();
    }

    public function actionLabel(string $action): string
    {
        return match ($action) {
            'ticket_called' => 'Dipanggil',
            'ticket_completed' => 'Selesai',
            'ticket_skipped' => 'Dilewati',
            'ticket_cancelled' => 'Dibatalkan',
            'ticket_recalled' => 'Dipanggil Ulang',
            default => ucwords(str_replace('_', ' ', $action)),
        };
    }

    public function actionColor(string $action): string
    {
        return match ($action) {
            'ticket_called', 'ticket_recalled' => 'blue',
            'ticket_completed' => 'green',
            'ticket_skipped', 'ticket_cancelled' => 'red',
            default => 'zinc',
        };
    }

    public function render(): View
    {
        return view('livewire.dashboard.admin-dashboard');
    }
}
