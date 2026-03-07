<?php

namespace App\Actions\Queue;

use App\Enums\QueueStatus;
use App\Models\QueueTicket;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CheckInQueueTicket
{
    public function __construct(
        private readonly LogQueueActivity $logQueueActivity
    ) {}

    public function handle(QueueTicket $queueTicket, ?int $userId = null): QueueTicket
    {
        if ($queueTicket->status !== QueueStatus::Booked) {
            throw new InvalidArgumentException('Only booked tickets can be checked in.');
        }

        return DB::transaction(function () use ($queueTicket, $userId): QueueTicket {
            $queueTicket->update([
                'status' => QueueStatus::Waiting,
                'checked_in_at' => CarbonImmutable::now(),
            ]);

            $this->logQueueActivity->handle(
                queueTicket: $queueTicket,
                action: 'ticket_checked_in',
                userId: $userId,
                counterId: null,
                meta: [
                    'from_status' => QueueStatus::Booked->value,
                    'to_status' => QueueStatus::Waiting->value,
                ]
            );

            return $queueTicket->refresh();
        });
    }
}
