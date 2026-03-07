<?php

namespace App\Support\Dashboard;

use App\Models\QueueActivity;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PetugasStats
{
    /**
     * @return array{
     *     served_today:int,
     *     action_counts:array{skipped:int,recalled:int,completed:int},
     *     service_distribution:array<string,int>
     * }
     */
    public function build(User|int $user, ?string $date = null): array
    {
        $userId = $user instanceof User ? $user->id : $user;
        $targetDate = $date ?? CarbonImmutable::now()->toDateString();

        $actions = QueueActivity::query()
            ->where('user_id', $userId)
            ->whereDate('created_at', $targetDate)
            ->selectRaw('action, COUNT(*) as aggregate')
            ->groupBy('action')
            ->pluck('aggregate', 'action');

        /** @var Collection<int,object{name:string,total:int}> $serviceRows */
        $serviceRows = DB::table('queue_activities')
            ->join('queue_tickets', 'queue_tickets.id', '=', 'queue_activities.queue_ticket_id')
            ->join('services', 'services.id', '=', 'queue_tickets.service_id')
            ->where('queue_activities.user_id', $userId)
            ->whereDate('queue_activities.created_at', $targetDate)
            ->whereIn('queue_activities.action', ['ticket_called', 'ticket_completed'])
            ->selectRaw('services.name as name, COUNT(*) as total')
            ->groupBy('services.name')
            ->get();

        /** @var array<string,int> $serviceDistribution */
        $serviceDistribution = $serviceRows
            ->mapWithKeys(fn (object $row): array => [$row->name => (int) $row->total])
            ->sortKeys()
            ->toArray();

        return [
            'served_today' => (int) ($actions['ticket_completed'] ?? 0),
            'action_counts' => [
                'skipped' => (int) ($actions['ticket_skipped'] ?? 0),
                'recalled' => (int) ($actions['ticket_recalled'] ?? 0),
                'completed' => (int) ($actions['ticket_completed'] ?? 0),
            ],
            'service_distribution' => $serviceDistribution,
        ];
    }
}
