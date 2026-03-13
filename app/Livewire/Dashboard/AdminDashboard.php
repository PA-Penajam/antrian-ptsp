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

    public function filterByDate(): void
    {
        unset(
            $this->todayTotal,
            $this->todayServed,
            $this->todayWaiting,
            $this->todayAvgWaitMinutes,
            $this->bookingSuccess,
            $this->bookingFailed,
            $this->byService,
            $this->byCounter,
            $this->byChannel,
            $this->trendData,
        );
    }

    #[Computed(persist: true)]
    public function todayTotal(): int
    {
        return QueueTicket::query()
            ->whereBetween('service_date', [$this->startDate, $this->endDate])
            ->count();
    }

    #[Computed(persist: true)]
    public function todayServed(): int
    {
        return QueueTicket::query()
            ->whereBetween('service_date', [$this->startDate, $this->endDate])
            ->where('status', QueueStatus::Completed)
            ->count();
    }

    #[Computed(persist: true)]
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

    #[Computed(persist: true)]
    public function todayAvgWaitMinutes(): float
    {
        $driver = DB::connection()->getDriverName();

        $diffExpression = $driver === 'sqlite'
            ? '(strftime(\'%s\', completed_at) - strftime(\'%s\', called_at)) / 60.0'
            : 'TIMESTAMPDIFF(MINUTE, called_at, completed_at)';

        $avg = QueueTicket::query()
            ->whereBetween('service_date', [$this->startDate, $this->endDate])
            ->where('status', QueueStatus::Completed)
            ->whereNotNull('called_at')
            ->whereNotNull('completed_at')
            ->selectRaw("AVG({$diffExpression}) as avg_minutes")
            ->value('avg_minutes');

        return round((float) ($avg ?? 0), 1);
    }

    #[Computed(persist: true)]
    public function bookingSuccess(): int
    {
        return QueueTicket::query()
            ->whereBetween('service_date', [$this->startDate, $this->endDate])
            ->where('channel', 'online_booking')
            ->where('status', '!=', QueueStatus::Cancelled)
            ->count();
    }

    #[Computed(persist: true)]
    public function bookingFailed(): int
    {
        return QueueTicket::query()
            ->whereBetween('service_date', [$this->startDate, $this->endDate])
            ->where('channel', 'online_booking')
            ->where('status', QueueStatus::Cancelled)
            ->count();
    }

    #[Computed(persist: true)]
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

    #[Computed(persist: true)]
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

    #[Computed(persist: true)]
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

    #[Computed(persist: true)]
    public function trendData(): array
    {
        $today = today()->toDateString();
        $isDefaultRange = $this->startDate === $today && $this->endDate === $today;

        if ($isDefaultRange) {
            $start = today()->subDays(6);
            $end = today();
        } else {
            $start = $this->startDate;
            $end = $this->endDate;
        }

        $counts = QueueTicket::query()
            ->selectRaw('DATE(service_date) as date, COUNT(*) as total')
            ->whereBetween('service_date', [$start, $end])
            ->groupByRaw('DATE(service_date)')
            ->pluck('total', 'date');

        $period = new \DatePeriod(
            new \DateTime((string) $start),
            new \DateInterval('P1D'),
            (new \DateTime((string) $end))->modify('+1 day'),
        );

        $data = [];
        foreach ($period as $day) {
            $date = $day->format('Y-m-d');
            $data[] = [
                'date' => $date,
                'total' => $counts[$date] ?? 0,
            ];
        }

        return $data;
    }

    /**
     * Intentionally not persisted — recentActivities must always reflect the
     * latest activity feed regardless of the selected date filter.
     */
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
