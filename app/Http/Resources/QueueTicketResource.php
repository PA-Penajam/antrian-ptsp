<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QueueTicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => encrypt($this->id),
            'ticket_number' => $this->ticket_number,
            'service_date' => $this->service_date?->format('Y-m-d'),
            'visitor_name' => $this->visitor_name,
            'visitor_wilayah_kode' => $this->visitor_wilayah_kode,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'service' => $this->whenLoaded('service', fn () => new ServiceResource($this->service)),
            'queue_position' => $this->resource->getQueuePosition(),
            'counter_name' => $this->whenLoaded('counter', fn () => $this->counter?->name),
            'checked_in_at' => $this->checked_in_at?->toIso8601String(),
            'called_at' => $this->called_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
        ];
    }
}
