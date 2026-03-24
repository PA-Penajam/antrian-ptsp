<?php

namespace App\Livewire;

use App\Enums\QueueStatus;
use App\Models\QueueTicket;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.tv-display')]
#[Title('Monitor Antrian')]
class TvDisplay extends Component
{
    public ?string $lastAnnouncedCall = null;

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

            $counterName = $firstCall->counter?->name ?? 'loket';
            $phoneticTicket = $this->formatForTts($firstCall->ticket_number);

            // MiniMax sangat sensitif terhadap format teks.
            // Gunakan koma untuk jeda pendek, dan ejaan yang eksplisit untuk tiket.
            $text = "Nomor antrian, {$phoneticTicket}. Silakan menuju, {$counterName}.";

            $this->dispatch('play-tts', text: $text);
        }
    }

    private function formatForTts(string $ticketNumber): string
    {
        // Untuk MiniMax: Pisahkan karakter dengan koma agar tidak dibaca sebagai singkatan aneh
        $clean = preg_replace('/[^A-Za-z0-9]/', '', $ticketNumber);

        $characters = str_split((string) $clean);

        // Ganti angka 0 menjadi 'nol' dan gabungkan dengan koma untuk jeda antar karakter
        $phonetic = array_map(function ($char) {
            return $char === '0' ? 'nol' : $char;
        }, $characters);

        return implode(', ', $phonetic);
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
            return [];
        }
    }
}
