<?php

namespace App\Actions\Queue;

use App\Enums\QueueStatus;
use App\Enums\UserRole;
use App\Models\Counter;
use App\Models\QueueTicket;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class CallNextTicket
{
    public function __construct(
        private readonly LogQueueActivity $logQueueActivity
    ) {}

    public function handle(Counter $counter, ?int $userId = null): ?QueueTicket
    {
        return DB::transaction(function () use ($counter, $userId): ?QueueTicket {
            $query = QueueTicket::query()
                ->where('queue_pool_id', $counter->queue_pool_id)
                ->where('status', QueueStatus::Waiting);

            if ($userId !== null) {
                $actor = User::query()->find($userId);

                if ($actor?->role === UserRole::Officer) {
                    $allowedServiceIds = $actor->services()
                        ->pluck('services.id');

                    if ($allowedServiceIds->isEmpty()) {
                        return null;
                    }

                    $query->whereIn('service_id', $allowedServiceIds);
                }
            }

            $queueTicket = $query
                ->orderBy('service_date')
                ->orderBy('sequence_number')
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if (! $queueTicket) {
                return null;
            }

            $fromStatus = $queueTicket->status;

            $queueTicket->update([
                'status' => QueueStatus::Called,
                'counter_id' => $counter->id,
                'called_at' => CarbonImmutable::now(),
            ]);

            $this->logQueueActivity->handle(
                queueTicket: $queueTicket,
                action: 'ticket_called',
                userId: $userId,
                counterId: $counter->id,
                meta: [
                    'from_status' => $fromStatus->value,
                    'to_status' => QueueStatus::Called->value,
                    'service_id' => $queueTicket->service_id,
                    'queue_pool_id' => $queueTicket->queue_pool_id,
                ]
            );

            return $queueTicket->refresh();
        });
    }
}
