<?php

namespace App\Console\Commands;

use App\Enums\QueueStatus;
use App\Enums\UserRole;
use App\Models\Counter;
use App\Models\CounterSession;
use App\Models\QueueActivity;
use App\Models\QueuePool;
use App\Models\QueueTicket;
use App\Models\Service;
use App\Models\User;
use App\Support\Reports\QueueReportBuilder;
use Database\Seeders\QueueMvpSeeder;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class QueueReportDummyDataCommand extends Command
{
    protected $signature = 'app:queue-report-dummy-data {--fresh : Drop all queue data before seeding}';

    protected $description = 'Setup dummy data for testing queue reports (pools, services, counters, tickets, activities)';

    public function handle(): int
    {
        if ($this->option('fresh')) {
            $this->warn('Clearing existing queue data...');
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table('queue_activities')->delete();
            DB::table('queue_tickets')->delete();
            DB::table('counter_sessions')->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->info('Setting up queue report dummy data...');

        // 1. Run QueueMvpSeeder first to create pools, services, counters
        $this->info('Creating pools, services, counters...');
        $this->call('db:seed', ['--class' => QueueMvpSeeder::class, '--no-interaction' => true]);

        // 2. Create officer users
        $this->info('Creating officer users...');
        $officers = $this->createOfficers();

        // 3. Create CounterSessions (officers logged in to counters)
        $this->info('Creating counter sessions...');
        $sessions = $this->createCounterSessions($officers);

        // 4. Create QueueTickets with various statuses
        $this->info('Creating queue tickets...');
        $tickets = $this->createQueueTickets();

        // 5. Create QueueActivities for completed tickets
        $this->info('Creating queue activities...');
        $this->createQueueActivities($tickets, $sessions);

        $this->newLine();
        $this->info('Queue report dummy data setup complete!');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Pools', QueuePool::count()],
                ['Services', Service::count()],
                ['Counters', Counter::count()],
                ['Officers', count($officers)],
                ['CounterSessions', CounterSession::count()],
                ['QueueTickets', QueueTicket::count()],
                ['QueueActivities', QueueActivity::count()],
            ]
        );

        // Verify by_officer equals by_status[completed]
        $this->newLine();
        $this->verifyReport();

        return Command::SUCCESS;
    }

    /**
     * @return array<string, User>
     */
    private function createOfficers(): array
    {
        $officerData = [
            ['name' => 'Officer Satu', 'email' => 'officer1@example.com'],
            ['name' => 'Officer Dua', 'email' => 'officer2@example.com'],
            ['name' => 'Officer Tiga', 'email' => 'officer3@example.com'],
            ['name' => 'Officer Empat', 'email' => 'officer4@example.com'],
            ['name' => 'Officer Lima', 'email' => 'officer5@example.com'],
        ];

        $officers = [];
        foreach ($officerData as $data) {
            $user = User::query()->firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'role' => UserRole::Officer->value,
                    'email_verified_at' => now(),
                    'password' => 'password',
                ]
            );
            $officers[$data['name']] = $user;
        }

        return $officers;
    }

    /**
     * @param  array<string, User>  $officers
     * @return array<string, CounterSession>
     */
    private function createCounterSessions(array $officers): array
    {
        $sessions = [];
        $counters = Counter::all();
        $officerNames = array_keys($officers);

        foreach ($counters as $index => $counter) {
            $officerName = $officerNames[$index % count($officerNames)];
            $officer = $officers[$officerName];

            $session = CounterSession::query()->firstOrCreate(
                [
                    'counter_id' => $counter->id,
                    'user_id' => $officer->id,
                ],
                [
                    'opened_at' => now()->startOfDay(),
                    'closed_at' => null,
                    'status' => 'active',
                ]
            );
            $sessions[$officerName] = $session;
        }

        return $sessions;
    }

    /**
     * @return Collection<int, QueueTicket>
     */
    private function createQueueTickets()
    {
        $services = Service::all();
        $counters = Counter::all();
        $today = now()->toDateString();
        $tickets = collect();

        $maxSequenceByPool = QueueTicket::query()
            ->whereDate('service_date', $today)
            ->selectRaw('queue_pool_id, MAX(sequence_number) as max_seq')
            ->groupBy('queue_pool_id')
            ->pluck('max_seq', 'queue_pool_id')
            ->toArray();

        $sequenceByPool = $maxSequenceByPool;

        foreach ($services as $service) {
            $poolId = $service->queue_pool_id;
            if (! isset($sequenceByPool[$poolId])) {
                $sequenceByPool[$poolId] = 0;
            }

            $statusesToCreate = [
                ['status' => QueueStatus::Completed, 'count' => 10],
                ['status' => QueueStatus::Completed, 'count' => 10],
                ['status' => QueueStatus::Completed, 'count' => 10],
                ['status' => QueueStatus::Completed, 'count' => 10],
                ['status' => QueueStatus::Completed, 'count' => 10],
                ['status' => QueueStatus::Called, 'count' => 3],
                ['status' => QueueStatus::Waiting, 'count' => 5],
                ['status' => QueueStatus::Cancelled, 'count' => 2],
                ['status' => QueueStatus::Skipped, 'count' => 1],
            ];

            foreach ($statusesToCreate as $item) {
                $status = $item['status'];
                $count = $item['count'];
                for ($i = 0; $i < $count; $i++) {
                    $sequenceByPool[$poolId]++;
                    $isWaiting = $status === QueueStatus::Waiting;
                    $counterId = $isWaiting ? null : $counters->random()->id;

                    $ticket = QueueTicket::query()->create([
                        'service_id' => $service->id,
                        'queue_pool_id' => $poolId,
                        'counter_id' => $counterId,
                        'created_by' => 1,
                        'channel' => 'walk_in',
                        'ticket_number' => strtoupper($service->code).str_pad($sequenceByPool[$poolId], 4, '0', STR_PAD_LEFT),
                        'sequence_number' => $sequenceByPool[$poolId],
                        'service_date' => $today,
                        'visitor_name' => 'Pengunjung '.fake()->name(),
                        'visitor_phone' => fake()->phoneNumber(),
                        'status' => $status->value,
                        'checked_in_at' => now()->subMinutes(rand(60, 600)),
                        'called_at' => ! $isWaiting ? now()->subMinutes(rand(10, 50)) : null,
                        'started_at' => ! $isWaiting ? now()->subMinutes(rand(5, 40)) : null,
                        'completed_at' => $status === QueueStatus::Completed ? now()->subMinutes(rand(1, 30)) : null,
                    ]);
                    $tickets->push($ticket);
                }
            }
        }

        return $tickets;
    }

    /**
     * @param  Collection<int, QueueTicket>  $tickets
     * @param  array<string, CounterSession>  $sessions
     */
    private function createQueueActivities($tickets, array $sessions): void
    {
        $completedTickets = $tickets->where('status', QueueStatus::Completed->value);

        foreach ($completedTickets as $ticket) {
            // Find the officer assigned to the counter
            $session = CounterSession::where('counter_id', $ticket->counter_id)->first();

            if ($session) {
                QueueActivity::query()->create([
                    'queue_ticket_id' => $ticket->id,
                    'user_id' => $session->user_id,
                    'counter_id' => $ticket->counter_id,
                    'action' => 'ticket_completed',
                    'meta' => ['completed_at' => $ticket->completed_at?->toIso8601String()],
                ]);
            }
        }
    }

    private function verifyReport(): void
    {
        $builder = app(QueueReportBuilder::class);
        $report = $builder->build(now()->subDays(1)->toDateString(), now()->addDays(1)->toDateString());

        $byStatusCompleted = $report['by_status'][QueueStatus::Completed->value] ?? 0;
        $byOfficerTotal = array_sum($report['by_officer']);

        $this->info('Report verification:');
        $this->line("  by_status[completed] = {$byStatusCompleted}");
        $this->line("  sum(by_officer) = {$byOfficerTotal}");

        if ($byStatusCompleted === $byOfficerTotal) {
            $this->info('  ✓ PASS: by_officer total matches by_status[completed]');
        } else {
            $this->warn("  ✗ FAIL: by_officer total ({$byOfficerTotal}) does not match by_status[completed] ({$byStatusCompleted})");
        }

        $this->newLine();
        $this->info('by_officer breakdown:');
        foreach ($report['by_officer'] as $officer => $count) {
            $this->line("  {$officer}: {$count}");
        }
    }
}
