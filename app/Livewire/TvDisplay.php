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
    public function render(): View
    {
        return view('livewire.tv-display', [
            'currentCalls' => $this->currentCalls(),
            'recentCalls' => $this->recentCalls(),
            'videos' => $this->videos(),
        ]);
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
