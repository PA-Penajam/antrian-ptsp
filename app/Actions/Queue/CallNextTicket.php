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
        private readonly LogQueueActivity $logQueueActivity,
        private readonly CompleteTicket $completeTicket,
    ) {}

    public function handle(Counter $counter, ?int $userId = null): ?QueueTicket
    {
        return DB::transaction(function () use ($counter, $userId): ?QueueTicket {
            $this->autoCompleteActiveTicket($counter, $userId);

            $query = QueueTicket::query()
                ->whereDate('service_date', CarbonImmutable::today())
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

                    // Pool-level access check: officer must have at least one service
                    // in the same pool as the counter they're trying to access.
                    $allowedPoolIds = $actor->services()
                        ->pluck('queue_pool_id')
                        ->unique()
                        ->values();

                    if ($allowedPoolIds->isNotEmpty() && ! $allowedPoolIds->contains($counter->queue_pool_id)) {
                        return null;
                    }

                    // Do not filter by specific service_id.
                    // The officer should serve ALL tickets that belong to the Counter's Queue Pool.
                }
            }

            $queueTicket = $query
                ->orderByDesc('service_date')
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
                    'visit_purpose' => $queueTicket->visit_purpose,
                ]
            );

            \App\Events\TicketCalled::dispatch($queueTicket->id);

            return $queueTicket->refresh();
        });
    }

    /**
     * Mark any currently-called ticket on this counter as completed before
     * calling the next one. This keeps the workflow tidy so officers do not
     * have to manually click "Selesai Dilayani" before every call.
     */
    private function autoCompleteActiveTicket(Counter $counter, ?int $userId): void
    {
        $activeTicket = QueueTicket::query()
            ->where('counter_id', $counter->id)
            ->where('status', QueueStatus::Called)
            ->whereDate('service_date', CarbonImmutable::today())
            ->lockForUpdate()
            ->first();

        if ($activeTicket === null) {
            return;
        }

        $this->completeTicket->handle($activeTicket, $counter, $userId);
    }
}
