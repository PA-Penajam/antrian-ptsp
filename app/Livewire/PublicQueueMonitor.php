<?php

namespace App\Livewire;

use App\Enums\QueueStatus;
use App\Models\QueueTicket;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class PublicQueueMonitor extends Component
{
    public string $quickTicketNumber = '';

    public ?string $lookupMessage = null;

    public ?array $quickResult = null;

    #[On('echo:public-queue,TicketCalled')]
    public function refreshQueue(): void
    {
        // Re-renders the component with latest live stats and active calls
    }

    public function searchTicket(): void
    {
        $this->lookupMessage = null;
        $this->quickResult = null;

        $number = trim($this->quickTicketNumber);
        if ($number === '') {
            $this->lookupMessage = 'Ketik nomor tiket Anda terlebih dahulu (contoh: A001 atau UMUM-0001).';

            return;
        }

        $today = CarbonImmutable::today();

        $ticket = QueueTicket::query()
            ->with(['service', 'counter', 'queuePool'])
            ->whereDate('service_date', $today)
            ->where(function ($query) use ($number) {
                $query->where('ticket_number', $number)
                    ->orWhere('ticket_number', 'like', "%{$number}%");
            })
            ->latest('id')
            ->first();

        if (! $ticket) {
            $this->lookupMessage = "Tiket \"{$number}\" tidak ditemukan untuk antrian hari ini. Pastikan nomor sudah sesuai atau cek tanggal kunjungan Anda.";

            return;
        }

        $queuePosition = 0;
        $guidanceMessage = '';

        if ($ticket->status === QueueStatus::Waiting && $ticket->queuePool) {
            $queuePosition = QueueTicket::query()
                ->where('queue_pool_id', $ticket->queue_pool_id)
                ->whereDate('service_date', $ticket->service_date)
                ->where('status', QueueStatus::Waiting)
                ->where('sequence_number', '<', $ticket->sequence_number)
                ->count() + 1;

            $ahead = max(0, $queuePosition - 1);
            if ($ahead === 0) {
                $guidanceMessage = 'Giliran Anda adalah yang berikutnya. Mohon bersiap di dekat loket.';
            } else {
                $guidanceMessage = "Ada {$ahead} antrian sebelum giliran Anda. Silakan menunggu dengan tenang di ruang PTSP.";
            }
        } elseif ($ticket->status === QueueStatus::Called) {
            $counterName = $ticket->counter?->name ?? 'Loket PTSP';
            $guidanceMessage = "Nomor Anda sedang dipanggil! Silakan langsung menuju ke {$counterName}.";
        } elseif ($ticket->status === QueueStatus::Completed) {
            $guidanceMessage = 'Pelayanan Anda telah selesai. Terima kasih telah tertib mematuhi antrian.';
        } elseif ($ticket->status === QueueStatus::Skipped) {
            $guidanceMessage = 'Nomor antrian terlewati. Silakan hubungi meja frontdesk PTSP untuk verifikasi ulang.';
        } else {
            $guidanceMessage = 'Status tiket: '.$ticket->status->label().'. Hubungi petugas frontdesk jika butuh bantuan.';
        }

        $this->quickResult = [
            'id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'visitor_name' => $ticket->visitor_name,
            'service_name' => $ticket->service?->name ?? 'Layanan PTSP',
            'status' => $ticket->status->value,
            'status_label' => $ticket->status->label(),
            'counter_name' => $ticket->counter?->name,
            'called_at' => $ticket->called_at?->format('H:i'),
            'queue_position' => $queuePosition,
            'guidance_message' => $guidanceMessage,
        ];
    }

    public function clearLookup(): void
    {
        $this->quickTicketNumber = '';
        $this->lookupMessage = null;
        $this->quickResult = null;
    }

    /**
     * @return array{total: int, waiting: int, completed: int}
     */
    protected function getTodayStats(): array
    {
        $today = CarbonImmutable::today();

        try {
            return [
                'total' => QueueTicket::query()->whereDate('service_date', $today)->count(),
                'waiting' => QueueTicket::query()->whereDate('service_date', $today)->where('status', QueueStatus::Waiting)->count(),
                'completed' => QueueTicket::query()->whereDate('service_date', $today)->where('status', QueueStatus::Completed)->count(),
            ];
        } catch (\Throwable) {
            return ['total' => 0, 'waiting' => 0, 'completed' => 0];
        }
    }

    /**
     * @return Collection<int, QueueTicket>
     */
    protected function getActiveCallingTickets(): Collection
    {
        $today = CarbonImmutable::today();

        try {
            return QueueTicket::query()
                ->with(['counter', 'service'])
                ->whereDate('service_date', $today)
                ->where('status', QueueStatus::Called)
                ->orderByDesc('called_at')
                ->limit(4)
                ->get();
        } catch (\Throwable) {
            return new Collection;
        }
    }

    public function render(): View
    {
        return view('livewire.public-queue-monitor', [
            'todayStats' => $this->getTodayStats(),
            'activeCallingTickets' => $this->getActiveCallingTickets(),
            'lastUpdated' => now()->format('H:i:s'),
        ]);
    }
}
