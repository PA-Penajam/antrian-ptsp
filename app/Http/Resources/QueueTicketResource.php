<?php

namespace App\Http\Resources;

use App\Enums\QueueStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QueueTicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'ticket_number' => $this->ticket_number,
            'service_date' => $this->service_date?->format('Y-m-d'),
            'visitor_name' => $this->visitor_name,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'service' => $this->whenLoaded('service', fn () => new ServiceResource($this->service)),
            'queue_position' => $this->computeQueuePosition(),
            'counter_name' => $this->whenLoaded('counter', fn () => $this->counter?->name),
            'checked_in_at' => $this->checked_in_at?->toIso8601String(),
            'called_at' => $this->called_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
        ];
    }

    private function computeQueuePosition(): ?int
    {
        if ($this->status !== QueueStatus::Waiting) {
            return null;
        }

        return \App\Models\QueueTicket::query()
            ->where('queue_pool_id', $this->queue_pool_id)
            ->whereDate('service_date', $this->service_date)
            ->where('status', QueueStatus::Waiting)
            ->where('sequence_number', '<', $this->sequence_number)
            ->count() + 1;
    }
}
