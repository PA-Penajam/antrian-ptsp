<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
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
            'name' => $this->name,
            'code' => $this->code,
            'slug' => $this->slug,
            'description' => $this->description,
            'requirements' => $this->requirements,
            'booking_enabled' => $this->booking_enabled,
            'daily_quota' => $this->daily_quota,
            'remaining_quota' => null, // Will be computed or fetched if needed, setting to null for now
        ];
    }
}
