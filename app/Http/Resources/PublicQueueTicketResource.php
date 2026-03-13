<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicQueueTicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => encrypt($this->id),
            'ticket_number' => $this->ticket_number,
            'service_date' => $this->service_date?->format('Y-m-d'),
            'visitor_name' => $this->maskedVisitorName(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'service' => $this->whenLoaded('service', fn () => new ServiceResource($this->service)),
            'queue_position' => $this->resource->getQueuePosition(),
            'counter_name' => $this->whenLoaded('counter', fn () => $this->counter?->name),
            'checked_in_at' => $this->checked_in_at?->toIso8601String(),
            'called_at' => $this->called_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
        ];
    }

    private function maskedVisitorName(): string
    {
        $name = (string) $this->visitor_name;
        if (mb_strlen($name) <= 3) {
            return str_repeat('*', mb_strlen($name));
        }

        return mb_substr($name, 0, 2).str_repeat('*', mb_strlen($name) - 2);
    }
}
