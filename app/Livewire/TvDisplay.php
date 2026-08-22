<?php

namespace App\Livewire;

use App\Enums\QueueStatus;
use App\Models\QueueTicket;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.tv-display')]
#[Title('Monitor Antrian')]
class TvDisplay extends Component
{
    public ?string $lastAnnouncedCall = null;

    #[On('echo:public-queue,TicketCalled')]
    public function refreshQueue(): void
    {
        // This empty method simply triggers a Livewire re-render
        // The render() method itself handles checkAndAnnounce() logic natively
    }

    public function render(): View
    {
        $currentCalls = $this->currentCalls();
        $this->checkAndAnnounce($currentCalls);

        return view('livewire.tv-display', [
            'currentCalls' => $currentCalls,
            'recentCalls' => $this->recentCalls(),
            'videos' => $this->videos(),
        ]);
    }

    protected function checkAndAnnounce(Collection $currentCalls): void
    {
        $firstCall = $currentCalls->first();
        if (! $firstCall) {
            return;
        }

        $callIdentifier = $firstCall->id.'-'.($firstCall->called_at?->timestamp ?? 0);

        if ($this->lastAnnouncedCall === null) {
            $this->lastAnnouncedCall = $callIdentifier;

            return;
        }

        if ($this->lastAnnouncedCall !== $callIdentifier) {
            $this->lastAnnouncedCall = $callIdentifier;

            $serviceName = $firstCall->service?->name ?? 'Loket';
            $ticketNumber = (string) $firstCall->ticket_number;
            $ticketNumber = preg_replace('/^([A-Za-z]+)0+(.*)$/', '$1$2', $ticketNumber);

            $text = "Nomor antrian {$ticketNumber}, silakan menuju Loket {$serviceName}.";

            $this->dispatch('play-tts', text: $text);
        }
    }

    protected function currentCalls(): Collection
    {
        try {
            return QueueTicket::query()
                ->with(['counter', 'service'])
                ->where('status', QueueStatus::Called)
                ->whereDate('service_date', today())
                ->orderByDesc('called_at')
                ->limit(6)
                ->get();
        } catch (\Throwable $e) {
            Log::warning('[TV] Gagal memuat antrian aktif', ['error' => $e->getMessage()]);

            return new Collection;
        }
    }

    protected function recentCalls(): Collection
    {
        try {
            return QueueTicket::query()
                ->with(['counter', 'service'])
                ->whereDate('service_date', today())
                ->whereNotNull('called_at')
                ->orderByDesc('called_at')
                ->limit(4)
                ->get();
        } catch (\Throwable $e) {
            Log::warning('[TV] Gagal memuat riwayat panggilan', ['error' => $e->getMessage()]);

            return new Collection;
        }
    }

    /**
     * @return list<string>
     */
    protected function videos(): array
    {
        try {
            return Cache::remember('tv-display:videos', 60, function (): array {
                $files = Storage::disk('public')->files('videos');

                $allowed = ['mp4', 'webm', 'ogg'];

                return collect($files)
                    ->filter(fn (string $file): bool => in_array(
                        strtolower(pathinfo($file, PATHINFO_EXTENSION)),
                        $allowed,
                        true,
                    ))
                    ->map(fn (string $file): string => asset('storage/'.$file))
                    ->sort()
                    ->values()
                    ->all();
            });
        } catch (\Throwable $e) {
            Log::warning('[TV] Gagal memuat daftar video', ['error' => $e->getMessage()]);

            return [];
        }
    }
}
