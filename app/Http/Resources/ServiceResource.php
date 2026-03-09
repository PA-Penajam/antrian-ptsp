<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'slug' => $this->slug,
            'description' => $this->description,
            'requirements' => $this->requirements,
            'booking_enabled' => (bool) $this->booking_enabled,
            'daily_quota' => $this->daily_quota,
            'remaining_quota' => $this->computeRemainingQuota(),
        ];
    }

    private function computeRemainingQuota(): ?int
    {
        if ($this->daily_quota === null) {
            return null;
        }

        $todayCount = \App\Models\QueueTicket::query()
            ->where('service_id', $this->id)
            ->whereDate('service_date', today())
            ->whereNotIn('status', [\App\Enums\QueueStatus::Cancelled])
            ->count();

        return max(0, $this->daily_quota - $todayCount);
    }
}
