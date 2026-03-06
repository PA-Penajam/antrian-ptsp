<?php

namespace App\Actions\Queue;

use App\Models\QueueTicket;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CreateQueueTicket
{
    public function __construct(
        private readonly GenerateTicketNumber $generateTicketNumber,
        private readonly LogQueueActivity $logQueueActivity
    ) {
    }

    /**
     * @param  array{
     *     service_id:int,
     *     channel:string,
     *     service_date:CarbonInterface|string,
     *     visitor_name:string,
     *     visitor_identifier:?string,
     *     visitor_phone:?string,
     *     notes:?string,
     *     created_by:?int
     * }  $payload
     */
    public function handle(array $payload): QueueTicket
    {
        $service = Service::query()->findOrFail($payload['service_id']);
        $channel = $payload['channel'];
        $serviceDate = $payload['service_date'] instanceof CarbonInterface
            ? CarbonImmutable::instance($payload['service_date'])
            : CarbonImmutable::parse((string) $payload['service_date']);

        $status = match ($channel) {
            'online_booking' => 'booked',
            'assisted_same_day', 'walk_in_kiosk' => 'waiting',
            default => throw new InvalidArgumentException('Unsupported ticket channel.'),
        };

        return DB::transaction(function () use ($payload, $service, $serviceDate, $channel, $status): QueueTicket {
            $numbering = $this->generateTicketNumber->handle($service->queuePool, $serviceDate);

            $ticket = QueueTicket::query()->create([
                'service_id' => $service->id,
                'queue_pool_id' => $service->queue_pool_id,
                'counter_id' => null,
                'created_by' => $payload['created_by'] ?? null,
                'channel' => $channel,
                'ticket_number' => $numbering['ticket_number'],
                'sequence_number' => $numbering['sequence_number'],
                'service_date' => $serviceDate->toDateString(),
                'visitor_name' => $payload['visitor_name'],
                'visitor_identifier' => $payload['visitor_identifier'] ?? null,
                'visitor_phone' => $payload['visitor_phone'] ?? null,
                'notes' => $payload['notes'] ?? null,
                'status' => $status,
                'checked_in_at' => null,
                'called_at' => null,
                'started_at' => null,
                'completed_at' => null,
                'cancelled_at' => null,
            ]);

            $this->logQueueActivity->handle(
                queueTicket: $ticket,
                action: 'ticket_created',
                userId: $payload['created_by'] ?? null,
                counterId: null,
                meta: [
                    'channel' => $channel,
                    'service_id' => $service->id,
                    'queue_pool_id' => $service->queue_pool_id,
                    'status' => $status,
                ]
            );

            return $ticket->refresh();
        });
    }
}
