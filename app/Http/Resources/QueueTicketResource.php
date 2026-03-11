<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QueueTicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ticket_number' => $this->ticket_number,
            'service_date' => $this->service_date ? $this->service_date->format('Y-m-d') : null,
            'visitor_name' => $this->visitor_name,
            'visitor_wilayah_kode' => $this->visitor_wilayah_kode,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'service' => new ServiceResource($this->whenLoaded('service')),
            'queue_position' => $this->sequence_number,
            'counter_name' => $this->whenLoaded('counter', fn () => $this->counter->name),
            'checked_in_at' => $this->checked_in_at,
            'called_at' => $this->called_at,
            'completed_at' => $this->completed_at,
            'cancelled_at' => $this->cancelled_at,
        ];
    }
}
