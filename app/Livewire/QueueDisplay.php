<?php

namespace App\Livewire;

use App\Enums\QueueStatus;
use App\Models\QueueTicket;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.public')]
#[Title('Display Antrian PTSP')]
class QueueDisplay extends Component
{
    public function render(): View
    {
        return view('livewire.queue-display', [
            'currentCalls' => $this->currentCalls(),
            'recentCalls' => $this->recentCalls(),
        ]);
    }

    protected function currentCalls(): Collection
    {
        return QueueTicket::query()
            ->with('counter')
            ->where('status', QueueStatus::Called)
            ->orderByDesc('called_at')
            ->limit(5)
            ->get();
    }

    protected function recentCalls(): Collection
    {
        return QueueTicket::query()
            ->with('counter')
            ->whereNotNull('called_at')
            ->orderByDesc('called_at')
            ->limit(10)
            ->get();
    }
}
