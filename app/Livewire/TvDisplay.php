<?php

namespace App\Livewire;

use App\Enums\QueueStatus;
use App\Models\QueueTicket;
use Illuminate\Database\Eloquent\Collection;
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
                ->limit(20)
                ->get();
        } catch (\Throwable $e) {
            return new Collection;
        }
    }
}
