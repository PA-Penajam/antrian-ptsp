<?php

use App\Actions\Queue\CallNextTicket;
use App\Actions\Queue\CancelTicket;
use App\Actions\Queue\CompleteTicket;
use App\Actions\Queue\RecallTicket;
use App\Actions\Queue\RestoreSkippedTicket;
use App\Actions\Queue\SkipTicket;
use App\Enums\QueueStatus;
use App\Models\Counter;
use App\Models\CounterSession;
use App\Models\QueueTicket;
use App\Models\User;
use App\Support\Dashboard\PetugasStats;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public ?int $lockedCounterId = null;

    public bool $fullScreen = false;

    public ?int $selectedCounterId = null;

    public ?string $sessionAssignmentType = null;

    public ?QueueTicket $activeTicket = null;

    public int $waitingCount = 0;

    /**
     * @var array<int,array{id:int,name:string,pool_name:string,pool_id:int}>
     */
    public array $counters = [];

    public string $feedbackTone = 'zinc';

    public string $feedbackMessage = '';

    private ?array $cachedStats = null;

    private ?string $cachedStatsDate = null;

    /**
     * @var array<int,array{id:int,ticket_number:string,sequence_number:int,service_name:string,visitor_name:string}>
     */
    public array $waitingTickets = [];

    /**
     * @var array<int,array{id:int,ticket_number:string,service_name:string}>
     */
    public array $skippedTickets = [];

    /**
     * @var array{
     *     served_today:int,
     *     action_counts:array{skipped:int,recalled:int,completed:int},
     *     service_distribution:array<string,int>
     * }
     */
    public array $stats = [
        'served_today' => 0,
        'action_counts' => [
            'skipped' => 0,
            'recalled' => 0,
            'completed' => 0,
        ],
        'service_distribution' => [],
    ];

    public function mount(PetugasStats $petugasStats, ?int $counterId = null, bool $fullScreen = false): void
    {
        $this->lockedCounterId = $counterId;
        $this->fullScreen = $fullScreen;

        $user = $this->currentUser();
        if ($user instanceof User && $this->lockedCounterId === null) {
            $activeSession = CounterSession::query()
                ->where('user_id', $user->id)
                ->where('status', 'open')
                ->whereDate('opened_at', today())
                ->first();

            if ($activeSession) {
                $this->selectedCounterId = $activeSession->counter_id;
                $this->sessionAssignmentType = $activeSession->assigned_by ? 'admin' : 'self';
            }
        }

        $this->syncBoard($petugasStats);
    }

    public function updatedSelectedCounterId(): void
    {
        $user = $this->currentUser();
        if ($user instanceof User && $this->lockedCounterId === null && $this->selectedCounterId !== null) {
            CounterSession::query()
                ->where('user_id', $user->id)
                ->where('status', 'open')
                ->update([
                    'status' => 'closed',
                    'closed_at' => now(),
                ]);

            CounterSession::query()->create([
                'counter_id' => $this->selectedCounterId,
                'user_id' => $user->id,
                'assigned_by' => null,
                'opened_at' => now(),
                'status' => 'open',
            ]);

            $this->sessionAssignmentType = 'self';
        }

        $this->refreshBoard();
    }

    public function refreshBoard(): void
    {
        $this->syncBoard(app(PetugasStats::class));
    }

    public function callNext(): void
    {
        $counter = $this->resolveSelectedCounter();
        if (! $counter) {
            $this->setFeedback('Pilih loket aktif terlebih dahulu.', 'amber');

            return;
        }

        try {
            $ticket = app(CallNextTicket::class)->handle($counter, $this->currentUserId());

            if (! $ticket) {
                $this->setFeedback('Tidak ada antrean menunggu yang sesuai layanan petugas.', 'amber');
            } else {
                $this->setFeedback("Nomor {$ticket->ticket_number} dipanggil ke {$counter->name}.", 'blue');
            }
        } catch (\Throwable $throwable) {
            $this->setFeedback($throwable->getMessage(), 'red');
        }

        $this->refreshBoard();
    }

    public function recall(): void
    {
        $counter = $this->resolveSelectedCounter();
        $ticket = $this->resolveActiveTicket();

        if (! $counter || ! $ticket) {
            $this->setFeedback('Tidak ada tiket aktif untuk dipanggil ulang.', 'amber');

            return;
        }

        try {
            app(RecallTicket::class)->handle($ticket, $counter, $this->currentUserId());
            $this->setFeedback("Nomor {$ticket->ticket_number} dipanggil ulang.", 'blue');
        } catch (\Throwable $throwable) {
            $this->setFeedback($throwable->getMessage(), 'red');
        }

        $this->refreshBoard();
    }

    public function skip(): void
    {
        $counter = $this->resolveSelectedCounter();
        $ticket = $this->resolveActiveTicket();

        if (! $counter || ! $ticket) {
            $this->setFeedback('Tidak ada tiket aktif untuk dilewati.', 'amber');

            return;
        }

        try {
            app(SkipTicket::class)->handle($ticket, $counter, $this->currentUserId());
            $this->setFeedback("Nomor {$ticket->ticket_number} dilewati.", 'amber');
        } catch (\Throwable $throwable) {
            $this->setFeedback($throwable->getMessage(), 'red');
        }

        $this->refreshBoard();
    }

    public function restoreSkipped(int $ticketId): void
    {
        $counter = $this->resolveSelectedCounter();

        if (! $counter) {
            $this->setFeedback('Pilih loket aktif terlebih dahulu.', 'amber');

            return;
        }

        $ticket = QueueTicket::query()->find($ticketId);

        if (! $ticket) {
            $this->setFeedback('Tiket tidak ditemukan.', 'amber');

            return;
        }

        try {
            app(RestoreSkippedTicket::class)->handle($ticket, $counter, $this->currentUserId());
            $this->setFeedback("Nomor {$ticket->ticket_number} dipanggil ulang.", 'blue');
        } catch (\Throwable $throwable) {
            $this->setFeedback($throwable->getMessage(), 'red');
        }

        $this->refreshBoard();
    }

    public function complete(): void
    {
        $counter = $this->resolveSelectedCounter();
        $ticket = $this->resolveActiveTicket();

        if (! $counter || ! $ticket) {
            $this->setFeedback('Tidak ada tiket aktif untuk diselesaikan.', 'amber');

            return;
        }

        try {
            app(CompleteTicket::class)->handle($ticket, $counter, $this->currentUserId());
            $this->setFeedback("Nomor {$ticket->ticket_number} ditandai selesai.", 'green');
        } catch (\Throwable $throwable) {
            $this->setFeedback($throwable->getMessage(), 'red');
        }

        $this->refreshBoard();
    }

    public function cancel(): void
    {
        $counter = $this->resolveSelectedCounter();
        $ticket = $this->resolveActiveTicket();

        if (! $counter || ! $ticket) {
            $this->setFeedback('Tidak ada tiket aktif untuk dibatalkan.', 'amber');

            return;
        }

        try {
            app(CancelTicket::class)->handle($ticket, $counter, $this->currentUserId());
            $this->setFeedback("Nomor {$ticket->ticket_number} dibatalkan.", 'red');
        } catch (\Throwable $throwable) {
            $this->setFeedback($throwable->getMessage(), 'red');
        }

        $this->refreshBoard();
    }

    #[Computed]
    public function hasSelectedCounter(): bool
    {
        return $this->selectedCounterId !== null;
    }

    #[Computed]
    public function isCounterLocked(): bool
    {
        return $this->lockedCounterId !== null;
    }

    #[Computed]
    public function selectedCounterName(): string
    {
        if ($this->selectedCounterId === null) {
            return '-';
        }

        $counter = collect($this->counters)
            ->firstWhere('id', $this->selectedCounterId);

        return is_array($counter)
            ? "{$counter['name']} - {$counter['pool_name']}"
            : '-';
    }

    private function syncBoard(PetugasStats $petugasStats): void
    {
        $user = $this->currentUser();
        if (! $user instanceof User) {
            $this->resetBoardState();

            return;
        }

        $today = now()->toDateString();
        $isAdmin = ($user->role?->value ?? $user->role) === 'admin';
        $allowedServiceIds = $user->services()->pluck('services.id');

        if (! $isAdmin && $allowedServiceIds->isEmpty()) {
            $this->resetBoardState();
            $this->setFeedback('Akun petugas belum memiliki layanan yang diizinkan.', 'amber');

            return;
        }

        $countersQuery = Counter::query()
            ->with('queuePool:id,name')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name');

        if (! $isAdmin) {
            $countersQuery->whereIn('queue_pool_id', function ($query) use ($allowedServiceIds): void {
                $query->select('queue_pool_id')
                    ->from('services')
                    ->whereIn('id', $allowedServiceIds)
                    ->whereNotNull('queue_pool_id');
            });
        }

        $availableCounters = $countersQuery->get(['id', 'name', 'queue_pool_id']);

        $this->counters = $availableCounters
            ->map(fn (Counter $counter): array => [
                'id' => $counter->id,
                'name' => $counter->name,
                'pool_name' => $counter->queuePool?->name ?? '-',
                'pool_id' => $counter->queue_pool_id,
            ])
            ->values()
            ->toArray();

        if (count($this->counters) === 0) {
            $this->selectedCounterId = null;
            $this->waitingCount = 0;
            $this->activeTicket = null;
            $this->waitingTickets = [];
            $this->skippedTickets = [];
            $this->stats = $this->resolveStatsWithCache($petugasStats, $user, $today);

            return;
        }

        $availableCounterIds = $availableCounters->pluck('id')->all();
        if ($this->lockedCounterId !== null) {
            if (! in_array($this->lockedCounterId, $availableCounterIds, true)) {
                $this->selectedCounterId = null;
                $this->waitingCount = 0;
                $this->activeTicket = null;
                $this->waitingTickets = [];
                $this->skippedTickets = [];
                $this->stats = $this->resolveStatsWithCache($petugasStats, $user, $today);
                $this->setFeedback('Anda tidak memiliki akses ke loket ini.', 'red');

                return;
            }

            $this->selectedCounterId = $this->lockedCounterId;
        }

        if (! in_array($this->selectedCounterId, $availableCounterIds, true)) {
            $this->selectedCounterId = $availableCounterIds[0];
        }

        $selectedCounter = $availableCounters->firstWhere('id', $this->selectedCounterId);
        if (! $selectedCounter instanceof Counter) {
            $this->waitingCount = 0;
            $this->activeTicket = null;
            $this->waitingTickets = [];
            $this->skippedTickets = [];
            $this->stats = $this->resolveStatsWithCache($petugasStats, $user, $today);

            return;
        }

        $queueBase = QueueTicket::query()
            ->whereDate('service_date', $today)
            ->where('queue_pool_id', $selectedCounter->queue_pool_id);

        // Do not filter by specific service_id for PTSP. 
        // Tickets are already scoped bounds to the selected counter's queue pool.

        $this->waitingCount = (clone $queueBase)
            ->where('status', QueueStatus::Waiting)
            ->count();

        $this->activeTicket = (clone $queueBase)
            ->with('service')
            ->where('counter_id', $selectedCounter->id)
            ->where('status', QueueStatus::Called)
            ->orderByDesc('called_at')
            ->orderByDesc('id')
            ->first();

        $this->waitingTickets = (clone $queueBase)
            ->with('service')
            ->where('status', QueueStatus::Waiting)
            ->orderBy('sequence_number')
            ->orderBy('id')
            ->limit(8)
            ->get()
            ->map(fn (QueueTicket $ticket): array => [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'sequence_number' => $ticket->sequence_number,
                'service_name' => $ticket->service?->name ?? '-',
                'visitor_name' => $ticket->visitor_name,
            ])
            ->toArray();

        $this->skippedTickets = (clone $queueBase)
            ->with('service')
            ->where('status', QueueStatus::Skipped)
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get()
            ->map(fn (QueueTicket $ticket): array => [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'service_name' => $ticket->service?->name ?? '-',
            ])
            ->toArray();

        $this->stats = $this->resolveStatsWithCache($petugasStats, $user, $today);
    }

    /**
     * @return array{
     *     served_today:int,
     *     action_counts:array{skipped:int,recalled:int,completed:int},
     *     service_distribution:array<string,int>
     * }
     */
    private function resolveStatsWithCache(PetugasStats $petugasStats, User $user, string $today): array
    {
        if ($this->cachedStatsDate === $today && $this->cachedStats !== null) {
            return $this->cachedStats;
        }

        $this->cachedStats = $petugasStats->build($user, $today);
        $this->cachedStatsDate = $today;

        return $this->cachedStats;
    }

    private function resolveSelectedCounter(): ?Counter
    {
        if ($this->selectedCounterId === null) {
            return null;
        }

        $cached = collect($this->counters)
            ->firstWhere('id', $this->selectedCounterId);

        if ($cached === null) {
            return null;
        }

        return Counter::query()
            ->where('is_active', true)
            ->find($this->selectedCounterId);
    }

    private function resolveActiveTicket(): ?QueueTicket
    {
        $ticketId = $this->activeTicket?->id;
        if (! $ticketId) {
            return null;
        }

        return QueueTicket::query()->find($ticketId);
    }

    private function resetBoardState(): void
    {
        $this->selectedCounterId = null;
        $this->activeTicket = null;
        $this->waitingCount = 0;
        $this->counters = [];
        $this->waitingTickets = [];
        $this->skippedTickets = [];
        $this->stats = [
            'served_today' => 0,
            'action_counts' => [
                'skipped' => 0,
                'recalled' => 0,
                'completed' => 0,
            ],
            'service_distribution' => [],
        ];
    }

    private function setFeedback(string $message, string $tone): void
    {
        $this->feedbackMessage = $message;
        $this->feedbackTone = $tone;
    }

    private function currentUser(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    private function currentUserId(): ?int
    {
        return $this->currentUser()?->id;
    }

    #[Computed]
    public function hasActiveTicket(): bool
    {
        return $this->activeTicket !== null;
    }
};
?>

<div 
    x-data="{
        calledAt: @js($activeTicket?->called_at?->toIso8601String()),
        elapsedSeconds: 0,
        timerInterval: null,
        soundEnabled: localStorage.getItem('officer_sound_enabled') !== 'false',
        showHotkeysModal: false,
        showOnboardingModal: false,
        onboardingStep: 1,
        showShiftBanner: false,
        todayDateStr: new Date().toISOString().slice(0, 10),
        flashedKey: null,
        toastMessage: '',
        toastTimeout: null,
        isBusy: false,
        audioCtx: null,
        
        init() {
            this.updateTimer();
            if (this.calledAt) {
                this.startTimer();
            }
            this.$watch('calledAt', (val) => {
                if (val) {
                    this.startTimer();
                } else {
                    this.stopTimer();
                }
            });
            
            // Check if onboarding or shift starter banner should appear
            const onboardingSeen = localStorage.getItem('officer_onboarding_seen') === 'true';
            const bannerDismissed = localStorage.getItem('officer_shift_banner_' + this.todayDateStr) === 'true';
            
            if (!onboardingSeen && @js($stats['served_today'] === 0 && ! $this->hasActiveTicket)) {
                this.showOnboardingModal = true;
            } else if (!bannerDismissed && @js($stats['served_today'] === 0 && ! $this->hasActiveTicket)) {
                this.showShiftBanner = true;
            }
        },
        
        openOnboarding(step = 1) {
            this.onboardingStep = step;
            this.showOnboardingModal = true;
        },
        
        nextOnboardingStep() {
            if (this.onboardingStep < 4) {
                this.onboardingStep++;
            } else {
                this.completeOnboarding();
            }
        },
        
        prevOnboardingStep() {
            if (this.onboardingStep > 1) {
                this.onboardingStep--;
            }
        },
        
        completeOnboarding() {
            try {
                localStorage.setItem('officer_onboarding_seen', 'true');
            } catch (e) {}
            this.showOnboardingModal = false;
            this.showToast('Selamat bertugas! Silakan panggil antrean pertama Anda 🎉');
        },
        
        dismissShiftBanner() {
            this.showShiftBanner = false;
            try {
                localStorage.setItem('officer_shift_banner_' + this.todayDateStr, 'true');
            } catch (e) {}
        },
        
        toggleSound() {
            this.soundEnabled = !this.soundEnabled;
            try {
                localStorage.setItem('officer_sound_enabled', this.soundEnabled);
            } catch (e) {}
            this.showToast(this.soundEnabled ? 'Suara notifikasi aktif 🔊' : 'Suara notifikasi dimatikan 🔇');
        },
        
        startTimer() {
            this.stopTimer();
            this.updateTimer();
            this.timerInterval = setInterval(() => this.updateTimer(), 1000);
        },
        
        stopTimer() {
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
                this.timerInterval = null;
            }
            this.elapsedSeconds = 0;
        },
        
        updateTimer() {
            if (!this.calledAt) {
                this.elapsedSeconds = 0;
                return;
            }
            const start = new Date(this.calledAt).getTime();
            const now = Date.now();
            this.elapsedSeconds = Math.max(0, Math.floor((now - start) / 1000));
        },
        
        get formattedTime() {
            const h = Math.floor(this.elapsedSeconds / 3600);
            const m = Math.floor((this.elapsedSeconds % 3600) / 60);
            const s = this.elapsedSeconds % 60;
            const pad = (n) => n.toString().padStart(2, '0');
            if (h > 0) {
                return `${pad(h)}:${pad(m)}:${pad(s)}`;
            }
            return `${pad(m)}:${pad(s)}`;
        },
        
        get timerColorClass() {
            if (this.elapsedSeconds < 600) return 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 border-emerald-300 dark:border-emerald-500/30';
            if (this.elapsedSeconds < 1200) return 'text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 border-amber-300 dark:border-amber-500/30';
            return 'text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-500/10 border-rose-300 dark:border-rose-500/30 animate-pulse';
        },
        
        get timerPacingLabel() {
            if (this.elapsedSeconds < 600) return 'Normal (< 10m)';
            if (this.elapsedSeconds < 1200) return 'Sedang (10-20m)';
            return 'Lama (> 20m)';
        },
        
        getAudioContext() {
            if (!this.audioCtx) {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (AudioCtx) {
                    this.audioCtx = new AudioCtx();
                }
            }
            if (this.audioCtx && this.audioCtx.state === 'suspended') {
                this.audioCtx.resume().catch(() => {});
            }
            return this.audioCtx;
        },
        
        playChime() {
            if (!this.soundEnabled) return;
            try {
                const ctx = this.getAudioContext();
                if (!ctx) return;
                const now = ctx.currentTime;
                
                const osc1 = ctx.createOscillator();
                const gain1 = ctx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(880, now);
                gain1.gain.setValueAtTime(0.12, now);
                gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.45);
                osc1.connect(gain1);
                gain1.connect(ctx.destination);
                osc1.start(now);
                osc1.stop(now + 0.45);
                
                const osc2 = ctx.createOscillator();
                const gain2 = ctx.createGain();
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(1320, now + 0.12);
                gain2.gain.setValueAtTime(0.15, now + 0.12);
                gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.65);
                osc2.connect(gain2);
                gain2.connect(ctx.destination);
                osc2.start(now + 0.12);
                osc2.stop(now + 0.65);
            } catch(e) {}
        },
        
        playSuccessChime() {
            if (!this.soundEnabled) return;
            try {
                const ctx = this.getAudioContext();
                if (!ctx) return;
                const now = ctx.currentTime;
                
                const notes = [523.25, 659.25, 783.99];
                notes.forEach((freq, i) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'triangle';
                    osc.frequency.setValueAtTime(freq, now + (i * 0.08));
                    gain.gain.setValueAtTime(0.1, now + (i * 0.08));
                    gain.gain.exponentialRampToValueAtTime(0.001, now + (i * 0.08) + 0.35);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start(now + (i * 0.08));
                    osc.stop(now + (i * 0.08) + 0.35);
                });
            } catch(e) {}
        },
        
        flashKey(keyName, actionLabel) {
            this.flashedKey = keyName;
            this.showToast(`[${keyName}] ${actionLabel}`);
            setTimeout(() => {
                if (this.flashedKey === keyName) this.flashedKey = null;
            }, 500);
        },
        
        showToast(msg) {
            this.toastMessage = msg;
            if (this.toastTimeout) clearTimeout(this.toastTimeout);
            this.toastTimeout = setTimeout(() => {
                this.toastMessage = '';
            }, 2500);
        },
        
        async executeAction(actionName, payload = null) {
            if (this.isBusy) return;
            this.isBusy = true;
            try {
                if (payload !== null) {
                    await $wire[actionName](payload);
                } else {
                    await $wire[actionName]();
                }
            } catch (e) {
                console.error('Action failed:', e);
            } finally {
                this.isBusy = false;
            }
        },
        
        handleKeydown(e) {
            const tag = e.target.tagName;
            if (['INPUT', 'TEXTAREA', 'SELECT'].includes(tag) || e.target.isContentEditable) return;
            if (e.isComposing) return;
            
            // Allow Escape to close modals
            if (e.key === 'Escape' && (this.showHotkeysModal || this.showOnboardingModal)) {
                this.showHotkeysModal = false;
                this.showOnboardingModal = false;
                return;
            }
            
            // Allow '?' to toggle hotkeys HUD
            if (e.key === '?' || (e.shiftKey && e.key === '/')) {
                e.preventDefault();
                this.showHotkeysModal = !this.showHotkeysModal;
                return;
            }
            
            // Ignore if busy or if modifier keys like Alt or Meta (Windows/Cmd) are pressed
            if (this.isBusy || e.altKey || e.metaKey) return;
            
            // Check if any dialog or modal is open
            const isAnyModalOpen = document.querySelector('[data-flux-modal][open], dialog[open]') || this.showHotkeysModal || this.showOnboardingModal;
            if (isAnyModalOpen) return;
            
            if (e.code === 'Space' || e.code === 'F2') {
                if (e.ctrlKey) return;
                e.preventDefault();
                this.flashKey(e.code === 'Space' ? 'Space' : 'F2', 'Panggil Berikutnya');
                this.playChime();
                this.executeAction('callNext');
            } else if (e.code === 'F1') {
                if (e.ctrlKey) return;
                e.preventDefault();
                this.flashKey('F1', 'Panggil Ulang');
                this.playChime();
                this.executeAction('recall');
            } else if (e.code === 'F3') {
                if (e.ctrlKey) return;
                e.preventDefault();
                this.flashKey('F3', 'Lewati Tiket');
                Flux.modal('confirm-skip-ticket').show();
            } else if (e.code === 'F4' || (e.ctrlKey && e.key === 'Enter')) {
                e.preventDefault();
                this.flashKey(e.code === 'F4' ? 'F4' : 'Ctrl+Enter', 'Selesai Dilayani');
                this.playSuccessChime();
                this.executeAction('complete');
            } else if (e.code === 'F8') {
                if (e.ctrlKey) return;
                e.preventDefault();
                this.flashKey('F8', 'Batalkan Tiket');
                Flux.modal('confirm-cancel-ticket').show();
            }
        }
    }"
    x-on:keydown.window="handleKeydown($event)"
    x-on:beforeunload.window="if (calledAt) { $event.preventDefault(); $event.returnValue = ''; }"
    wire:poll.10s.visible="refreshBoard"
    class="relative space-y-6"
>
    <!-- Offline Resilient Banner -->
    <div wire:offline class="rounded-2xl border border-amber-500/40 bg-amber-500/10 p-4 text-amber-800 dark:text-amber-300 shadow-sm flex items-center justify-between gap-3 animate-fade-in">
        <div class="flex items-center gap-3">
            <span class="size-2.5 rounded-full bg-amber-500 animate-ping"></span>
            <div>
                <p class="text-sm font-bold">Koneksi Terputus (Mode Offline)</p>
                <p class="text-xs text-amber-700/80 dark:text-amber-400">Sistem akan otomatis tersambung kembali saat internet tersedia.</p>
            </div>
        </div>
        <flux:button wire:click="refreshBoard" size="sm" variant="subtle" color="amber" icon="arrow-path">
            Coba Hubungkan
        </flux:button>
    </div>

    <!-- Floating HUD Action Toast -->
    <div 
        x-cloak
        x-show="toastMessage !== ''"
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="opacity-0 translate-y-3 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
        class="fixed bottom-6 right-6 z-50 flex items-center gap-2.5 rounded-2xl border border-cyan-500/40 bg-zinc-900/95 px-4 py-2.5 text-sm font-semibold text-cyan-300 shadow-2xl backdrop-blur-md dark:bg-zinc-950/95 dark:text-cyan-200"
    >
        <span class="size-2 rounded-full bg-cyan-400 animate-ping"></span>
        <span x-text="toastMessage"></span>
    </div>

    <!-- Header Navigation & Controls Strip -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <flux:heading size="xl" level="1" class="font-extrabold tracking-tight">Workstation Petugas</flux:heading>
                @if ($this->hasSelectedCounter)
                    <flux:badge color="cyan" size="sm" class="font-semibold shadow-xs">
                        <span class="size-1.5 rounded-full bg-cyan-500 animate-pulse mr-1.5"></span>
                        {{ $this->selectedCounterName }}
                    </flux:badge>
                @endif
            </div>
            <flux:subheading class="mt-1 text-zinc-500 dark:text-zinc-400">
                Pusat kendali panggilan loket, durasi stopwatch live, dan manajemen antrean terpadu.
            </flux:subheading>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Onboarding Quick-Start Tour Button -->
            <flux:button
                x-on:click="openOnboarding()"
                variant="subtle"
                size="sm"
                icon="sparkles"
                class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900 text-cyan-700 dark:text-cyan-400 font-semibold"
                title="Buka Panduan Interaktif Loket"
            >
                <span class="hidden sm:inline text-xs">Panduan Loket</span>
            </flux:button>

            <!-- Sound FX Toggle Button -->
            <flux:button
                x-on:click="toggleSound()"
                variant="subtle"
                size="sm"
                class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900"
                aria-label="Toggle Sound Effects"
            >
                <span x-show="soundEnabled" class="inline-flex items-center"><flux:icon.speaker-wave class="size-4" /></span>
                <span x-show="!soundEnabled" class="inline-flex items-center"><flux:icon.speaker-x-mark class="size-4" /></span>
                <span class="hidden md:inline text-xs" x-text="soundEnabled ? 'Audio Aktif' : 'Audio Senyap'"></span>
            </flux:button>

            <!-- Hotkeys HUD Modal Button -->
            <flux:button
                x-on:click="showHotkeysModal = true"
                variant="subtle"
                size="sm"
                icon="command-line"
                class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900"
            >
                <span class="hidden sm:inline text-xs">Hotkeys</span>
                <kbd class="ml-1 text-xs font-mono px-1 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700">?</kbd>
            </flux:button>

            @if (! $fullScreen && $this->hasSelectedCounter)
                <flux:button :href="route('officer.counter.show', ['counter' => $selectedCounterId])" variant="subtle" size="sm" icon="arrows-pointing-out" class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <span class="hidden sm:inline">Layar Penuh</span>
                </flux:button>
            @endif

            <flux:badge color="zinc" icon="arrow-path" class="text-xs font-mono">
                10s sync
            </flux:badge>
        </div>
    </div>

    <!-- First-Run / Shift Starter Activation Banner -->
    <div 
        x-cloak
        x-show="showShiftBanner"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="relative overflow-hidden rounded-3xl border border-cyan-500/30 bg-gradient-to-r from-cyan-500/10 via-sky-500/5 to-emerald-500/10 p-4 sm:p-5 shadow-sm dark:border-cyan-500/20"
    >
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-start sm:items-center gap-3.5">
                <div class="flex size-10 shrink-0 items-center justify-center rounded-2xl bg-cyan-500/20 text-cyan-600 dark:text-cyan-400">
                    <flux:icon.sparkles class="size-5" />
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-bold text-zinc-900 dark:text-white">Siap Memulai Shift Pelayanan Hari Ini?</span>
                        <flux:badge size="sm" color="cyan" class="font-semibold">Shift Starter</flux:badge>
                    </div>
                    <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-0.5">
                        Tekan tombol <kbd class="px-1.5 py-0.5 rounded bg-zinc-200 dark:bg-zinc-800 text-xs font-mono font-bold text-cyan-700 dark:text-cyan-300">Space</kbd> untuk memanggil antrean pertama atau buka tur panduan loket.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <flux:button size="sm" variant="filled" color="cyan" x-on:click="openOnboarding()" class="font-semibold shadow-xs">
                    Buka Tur Loket
                </flux:button>
                <flux:button size="sm" variant="ghost" x-on:click="dismissShiftBanner()">
                    Tutup
                </flux:button>
            </div>
        </div>
    </div>

    @if ($feedbackMessage !== '')
        <div class="animate-fade-in-up">
            <flux:callout icon="information-circle" color="{{ $feedbackTone }}" class="shadow-xs rounded-2xl flex items-center justify-between">
                <div>{{ $feedbackMessage }}</div>
                <button wire:click="$set('feedbackMessage', '')" class="ml-3 text-xs opacity-70 hover:opacity-100 transition-opacity" title="Tutup Notifikasi">
                    <flux:icon.x-mark class="size-4" />
                </button>
            </flux:callout>
        </div>
    @endif

    <!-- Main Cockpit: Balanced 2-Column Desktop Grid -->
    <div class="grid gap-6 lg:grid-cols-2 items-start">
        
        <!-- Left: Loket Session, Calling Cockpit & Hotkeys Guide -->
        <div class="space-y-6">
            
            <!-- Loket Selector Strip (if not locked) -->
            <flux:card class="admin-card-elevated border border-zinc-200 dark:border-zinc-800/80 bg-white dark:bg-zinc-900 p-4 rounded-2xl">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    @if ($this->isCounterLocked)
                        <div class="flex items-center gap-3">
                            <div class="admin-icon-box bg-cyan-100 text-cyan-700 dark:bg-cyan-950/60 dark:text-cyan-400">
                                <flux:icon.building-office class="size-5" />
                            </div>
                            <div>
                                <flux:text class="text-xs uppercase font-bold tracking-wider text-zinc-400">Loket Aktif Anda</flux:text>
                                <flux:heading size="md" class="text-zinc-900 dark:text-white font-bold">{{ $this->selectedCounterName }}</flux:heading>
                            </div>
                        </div>
                        <flux:badge color="cyan" size="sm" class="self-start sm:self-auto font-semibold">Terkunci</flux:badge>
                    @else
                        <div class="w-full">
                            <div class="flex items-center justify-between gap-2 mb-1.5">
                                <flux:label class="text-xs font-bold uppercase tracking-wider text-zinc-500">Pilih Loket Bertugas</flux:label>
                                @if ($sessionAssignmentType === 'admin')
                                    <flux:badge size="sm" color="violet">Ditunjuk Admin</flux:badge>
                                @elseif ($sessionAssignmentType === 'self')
                                    <flux:badge size="sm" color="emerald">Dipilih Sendiri</flux:badge>
                                @endif
                            </div>
                            <flux:select wire:model.live="selectedCounterId" class="w-full">
                                @if (count($counters) === 0)
                                    <flux:select.option value="">Belum ada loket aktif</flux:select.option>
                                @else
                                    @foreach ($counters as $counter)
                                        <flux:select.option value="{{ $counter['id'] }}">
                                            {{ $counter['name'] }} - {{ $counter['pool_name'] }}
                                        </flux:select.option>
                                    @endforeach
                                @endif
                            </flux:select>
                        </div>
                    @endif
                </div>
            </flux:card>

            <!-- Hero Calling Cockpit Stage -->
            @if ($this->hasActiveTicket)
                <!-- ACTIVE TICKET STAGE: Electric Cyan & Emerald Glow -->
                <div class="relative overflow-hidden rounded-3xl border-2 border-cyan-500/50 dark:border-cyan-500/40 bg-gradient-to-br from-cyan-50/90 via-white to-emerald-50/70 p-6 md:p-8 shadow-xl shadow-cyan-950/5 dark:from-cyan-950/40 dark:via-zinc-900 dark:to-emerald-950/30 transition-all duration-300 animate-fade-in-up">
                    <!-- Subtle Ambient Background Light -->
                    <div class="absolute -right-16 -top-16 size-64 rounded-full bg-cyan-500/10 blur-3xl pointer-events-none"></div>
                    <div class="absolute -left-16 -bottom-16 size-64 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>

                    <!-- Top Stage Row: Status Beacon + Stopwatch -->
                    <div class="relative z-10 flex flex-wrap items-center justify-between gap-3 border-b border-cyan-200/60 pb-4 dark:border-cyan-900/40">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/15 px-3 py-1 text-xs font-bold uppercase tracking-wider text-emerald-700 shadow-xs dark:text-emerald-300">
                                <span class="size-2 rounded-full bg-emerald-500 animate-ping"></span>
                                Sedang Melayani
                            </span>
                            <flux:badge size="sm" color="zinc" class="font-mono text-xs">
                                Urutan #{{ $activeTicket->sequence_number }}
                            </flux:badge>
                        </div>

                        <!-- Live Service Stopwatch -->
                        <div class="flex items-center gap-2">
                            <div class="inline-flex items-center gap-2 rounded-2xl border px-3.5 py-1.5 text-xs font-semibold shadow-xs" :class="timerColorClass">
                                <flux:icon.clock class="size-3.5 animate-stopwatch-beat" />
                                <span>Durasi:</span>
                                <span class="font-mono text-sm font-black tabular-nums tracking-wider" x-text="formattedTime">00:00</span>
                                <span class="text-xs opacity-75 font-normal" x-text="timerPacingLabel"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Center Hero: Massive Ticket Number & Visitor Profile -->
                    <div class="relative z-10 my-6 flex flex-col items-center justify-center text-center">
                        <div class="text-xs font-bold uppercase tracking-[0.25em] text-cyan-600 dark:text-cyan-400">
                            Tiket Aktif
                        </div>
                        <div class="mt-1 font-mono text-6xl font-black tracking-tight text-cyan-600 sm:text-8xl dark:text-cyan-300 drop-shadow-sm select-all break-all sm:break-normal max-w-full animate-ticket-arrive">
                            {{ $activeTicket->ticket_number }}
                        </div>
                        
                        <div class="mt-3 flex flex-wrap items-center justify-center gap-2 max-w-xl">
                            <flux:heading size="xl" class="font-extrabold text-zinc-900 dark:text-white break-words">
                                {{ $activeTicket->service?->name ?? 'Layanan Umum' }}
                            </flux:heading>
                        </div>

                        <!-- Visitor Details Meta Row -->
                        <div class="mt-4 flex flex-wrap items-center justify-center gap-4 text-sm text-zinc-600 dark:text-zinc-300">
                            @if (!empty($activeTicket->visitor_name))
                                <div class="inline-flex items-center gap-1.5 rounded-xl bg-zinc-100/80 px-3 py-1 dark:bg-zinc-800/80 max-w-xs" title="{{ $activeTicket->visitor_name }}">
                                    <flux:icon.user class="size-4 text-zinc-400 shrink-0" />
                                    <span class="font-semibold text-zinc-800 dark:text-zinc-200 truncate">{{ $activeTicket->visitor_name }}</span>
                                </div>
                            @endif

                            @if (!empty($activeTicket->visit_purpose))
                                <div class="inline-flex items-center gap-1.5 rounded-xl bg-zinc-100/80 px-3 py-1 dark:bg-zinc-800/80 max-w-md" title="{{ $activeTicket->visit_purpose }}">
                                    <flux:icon.document-text class="size-4 text-zinc-400 shrink-0" />
                                    <span class="line-clamp-1">{{ Str::limit($activeTicket->visit_purpose, 45) }}</span>
                                </div>
                            @endif

                            @if (!empty($activeTicket->channel))
                                <flux:badge size="sm" color="zinc" class="capitalize">
                                    {{ $activeTicket->channel }}
                                </flux:badge>
                            @endif
                        </div>
                    </div>

                    <!-- Action Controls Cockpit Bar -->
                    <div class="relative z-10 grid gap-3 pt-4 border-t border-cyan-200/60 dark:border-cyan-900/40 sm:grid-cols-2 lg:grid-cols-5">
                        <flux:button
                            variant="primary"
                            icon="megaphone"
                            wire:click="callNext"
                            :disabled="! $this->hasSelectedCounter"
                            wire:loading.attr="disabled"
                            class="w-full bg-gradient-to-r from-cyan-600 to-sky-600 hover:from-cyan-500 hover:to-sky-500 text-white font-bold shadow-md shadow-cyan-600/25 active:scale-95 transition-all"
                            :class="(flashedKey === 'Space' || flashedKey === 'F2') ? 'animate-hotkey-cyan ring-4 ring-cyan-400/50' : ''"
                        >
                            <span>Panggil Berikutnya</span>
                            <kbd class="workstation-kbd bg-cyan-800/40 text-cyan-100 border-cyan-400/40 ml-1.5">Space</kbd>
                        </flux:button>

                        <flux:button
                            variant="filled"
                            color="cyan"
                            icon="speaker-wave"
                            wire:click="recall"
                            :disabled="! $this->hasActiveTicket"
                            wire:loading.attr="disabled"
                            class="w-full font-semibold active:scale-95 transition-all"
                            :class="flashedKey === 'F1' ? 'animate-hotkey-sky ring-4 ring-sky-400/50' : ''"
                        >
                            <span>Panggil Ulang</span>
                            <kbd class="workstation-kbd bg-sky-800/30 text-sky-200 border-sky-400/30 ml-1.5">F1</kbd>
                        </flux:button>

                        <flux:button
                            variant="ghost"
                            icon="forward"
                            x-on:click="Flux.modal('confirm-skip-ticket').show()"
                            :disabled="! $this->hasActiveTicket"
                            wire:loading.attr="disabled"
                            class="w-full text-amber-600 hover:bg-amber-50 hover:text-amber-700 dark:text-amber-400 dark:hover:bg-amber-950/30 dark:hover:text-amber-300 font-semibold active:scale-95 transition-all"
                            :class="flashedKey === 'F3' ? 'animate-hotkey-amber ring-4 ring-amber-400/50' : ''"
                        >
                            <span>Lewati (Skip)</span>
                            <kbd class="workstation-kbd bg-amber-800/30 text-amber-200 border-amber-400/30 ml-1.5">F3</kbd>
                        </flux:button>

                        <flux:button
                            variant="filled"
                            color="emerald"
                            icon="check-circle"
                            wire:click="complete"
                            :disabled="! $this->hasActiveTicket"
                            wire:loading.attr="disabled"
                            class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold shadow-md shadow-emerald-600/25 active:scale-95 transition-all"
                            :class="(flashedKey === 'F4' || flashedKey === 'Ctrl+Enter') ? 'animate-hotkey-emerald ring-4 ring-emerald-400/50' : ''"
                        >
                            <span>Selesai</span>
                            <kbd class="workstation-kbd bg-emerald-800/40 text-emerald-100 border-emerald-400/40 ml-1.5">F4</kbd>
                        </flux:button>

                        <flux:button
                            variant="ghost"
                            icon="x-circle"
                            x-on:click="Flux.modal('confirm-cancel-ticket').show()"
                            :disabled="! $this->hasActiveTicket"
                            wire:loading.attr="disabled"
                            class="w-full text-red-600 hover:bg-red-50 hover:text-red-700 dark:text-red-500 dark:hover:bg-red-950/30 dark:hover:text-red-400 font-semibold active:scale-95 transition-all"
                            :class="flashedKey === 'F8' ? 'animate-hotkey-rose ring-4 ring-rose-400/50' : ''"
                        >
                            <span>Batalkan</span>
                            <kbd class="workstation-kbd bg-rose-800/30 text-rose-200 border-rose-400/30 ml-1.5">F8</kbd>
                        </flux:button>
                    </div>
                </div>
            @else
                <!-- STANDBY STAGE: Ready to Serve Cockpit -->
                <div class="relative overflow-hidden rounded-3xl border-2 border-dashed border-zinc-300 dark:border-zinc-800 bg-gradient-to-b from-zinc-50/80 via-white to-zinc-100/50 p-8 text-center dark:from-zinc-900/60 dark:via-zinc-900/40 dark:to-zinc-950/80 shadow-sm transition-all duration-300 animate-fade-in-up">
                    <div class="mx-auto flex size-16 items-center justify-center rounded-3xl bg-cyan-100 text-cyan-600 shadow-sm dark:bg-cyan-950/60 dark:text-cyan-400 animate-workstation-beacon">
                        <flux:icon.megaphone class="size-8" />
                    </div>

                    <flux:heading size="xl" class="mt-4 font-extrabold text-zinc-900 dark:text-white">
                        Loket Siap Melayani
                    </flux:heading>
                    <flux:text class="mx-auto mt-1 max-w-md text-sm text-zinc-500 dark:text-zinc-400">
                        Tidak ada tiket yang sedang aktif. Tekan tombol panggil atau tekan hotkey <kbd class="px-1.5 py-0.5 rounded bg-zinc-200 dark:bg-zinc-800 text-xs font-mono font-bold">Space</kbd> untuk memanggil antrean berikutnya.
                    </flux:text>

                    <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-zinc-200 dark:border-zinc-700 bg-zinc-100/80 dark:bg-zinc-800/80 px-3.5 py-1 text-xs font-semibold text-zinc-600 dark:text-zinc-300">
                            Tiket Aktif: <strong class="text-zinc-800 dark:text-zinc-100">Belum ada</strong>
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-500/30 bg-amber-500/10 px-3.5 py-1 text-xs font-semibold text-amber-700 dark:text-amber-300">
                            <span class="size-2 rounded-full bg-amber-500 {{ $waitingCount > 0 ? 'animate-pulse' : '' }}"></span>
                            {{ $waitingCount }} Antrean Menunggu di Pool Ini
                        </span>
                    </div>

                    <div class="mt-6 max-w-sm mx-auto">
                        <flux:button
                            variant="primary"
                            icon="megaphone"
                            wire:click="callNext"
                            :disabled="! $this->hasSelectedCounter || $waitingCount === 0"
                            wire:loading.attr="disabled"
                            class="w-full py-3 text-base bg-gradient-to-r from-cyan-600 to-sky-600 hover:from-cyan-500 hover:to-sky-500 text-white font-bold shadow-lg shadow-cyan-600/30 active:scale-95 transition-all"
                            :class="(flashedKey === 'Space' || flashedKey === 'F2') ? 'animate-hotkey-cyan ring-4 ring-cyan-400/50' : ''"
                        >
                            <span>Panggil Antrean Berikutnya</span>
                            <kbd class="workstation-kbd bg-cyan-800/40 text-cyan-100 border-cyan-400/40 ml-2">Space / F2</kbd>
                        </flux:button>
                    </div>
                </div>
            @endif

            <!-- Quick Hotkey Cheat Sheet Card -->
            <flux:card class="admin-card-elevated p-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/70 dark:bg-zinc-900/50 space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Pintasan Keyboard Loket</span>
                    <button type="button" x-on:click="showHotkeysModal = true" class="text-xs text-cyan-600 hover:text-cyan-700 dark:text-cyan-400 dark:hover:text-cyan-300 font-semibold inline-flex items-center gap-1">
                        <span>Lihat Semua</span>
                        <flux:icon.arrow-top-right-on-square class="size-3" />
                    </button>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="flex items-center justify-between p-2 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700/80 shadow-2xs">
                        <span class="text-zinc-600 dark:text-zinc-300 font-medium">Panggil Baru</span>
                        <kbd class="font-mono font-bold px-1.5 py-0.5 rounded bg-zinc-100 dark:bg-zinc-900 border text-cyan-600 dark:text-cyan-400">Space</kbd>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700/80 shadow-2xs">
                        <span class="text-zinc-600 dark:text-zinc-300 font-medium">Panggil Ulang</span>
                        <kbd class="font-mono font-bold px-1.5 py-0.5 rounded bg-zinc-100 dark:bg-zinc-900 border text-sky-600 dark:text-sky-400">F1</kbd>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700/80 shadow-2xs">
                        <span class="text-zinc-600 dark:text-zinc-300 font-medium">Lewati Tiket</span>
                        <kbd class="font-mono font-bold px-1.5 py-0.5 rounded bg-zinc-100 dark:bg-zinc-900 border text-amber-600 dark:text-amber-400">F3</kbd>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700/80 shadow-2xs">
                        <span class="text-zinc-600 dark:text-zinc-300 font-medium">Selesai Layani</span>
                        <kbd class="font-mono font-bold px-1.5 py-0.5 rounded bg-zinc-100 dark:bg-zinc-900 border text-emerald-600 dark:text-emerald-400">F4</kbd>
                    </div>
                </div>
            </flux:card>
        </div>

        <!-- Right: Queue Streams & Performance Analytics -->
        <div class="space-y-6">
            
            <!-- Performance & Analytics Card -->
            <flux:card class="admin-card-elevated space-y-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900">
                <div class="flex items-center gap-3 border-b border-zinc-100 pb-3 dark:border-zinc-800/80">
                    <div class="admin-icon-box bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
                        <flux:icon.chart-bar class="size-5" />
                    </div>
                    <div>
                        <flux:heading size="lg" class="font-bold">Kinerja Hari Ini</flux:heading>
                        <flux:text class="text-xs text-zinc-500">Statistik real-time loket petugas.</flux:text>
                    </div>
                </div>
                
                <!-- Completed Count Hero Box -->
                <div class="admin-stat-success rounded-2xl p-5 border shadow-sm">
                    <div class="flex items-center justify-between">
                        <flux:text class="text-xs font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">Total Selesai Dilayani</flux:text>
                        <flux:badge size="sm" color="emerald" class="font-bold">Hari Ini</flux:badge>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <div class="font-mono text-4xl font-black text-emerald-900 dark:text-emerald-200 tabular-nums">{{ $stats['served_today'] }}</div>
                        <span class="text-xs font-medium text-emerald-700 dark:text-emerald-400">pemohon</span>
                    </div>
                    @if ($stats['served_today'] >= 10)
                        <div class="mt-3 flex items-center gap-1.5 text-xs font-semibold text-emerald-800 dark:text-emerald-300">
                            <span>🎉 Pencapaian prima: {{ $stats['served_today'] }} tiket tuntas!</span>
                        </div>
                    @endif
                </div>
                
                <!-- Secondary Counters Grid -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="admin-stat-warning rounded-xl p-3 text-center border shadow-xs">
                        <div class="text-xs font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300">Dilewati (Skip)</div>
                        <div class="mt-1 font-mono text-2xl font-black text-amber-900 dark:text-amber-200 tabular-nums">{{ $stats['action_counts']['skipped'] }}</div>
                    </div>
                    <div class="admin-stat-total rounded-xl p-3 text-center border shadow-xs">
                        <div class="text-xs font-bold uppercase tracking-wider text-sky-800 dark:text-sky-300">Dipanggil Ulang</div>
                        <div class="mt-1 font-mono text-2xl font-black text-sky-900 dark:text-sky-200 tabular-nums">{{ $stats['action_counts']['recalled'] }}</div>
                    </div>
                </div>
            </flux:card>

            <!-- Waiting Queue Priority Stream (Main Table) -->
            <flux:card class="admin-card-elevated space-y-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900">
                <div class="flex items-center justify-between gap-3 border-b border-zinc-100 pb-3 dark:border-zinc-800/80">
                    <div class="flex items-center gap-3">
                        <div class="admin-icon-box bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400">
                            <flux:icon.list-bullet class="size-5" />
                        </div>
                        <div>
                            <flux:heading size="lg" class="font-bold">Antrean Menunggu Prioritas</flux:heading>
                            <flux:text class="text-xs text-zinc-500">Daftar urutan kedatangan pemohon yang siap dipanggil.</flux:text>
                        </div>
                    </div>
                    <flux:badge color="zinc" size="sm" class="font-mono font-bold">{{ count($waitingTickets) }} antrean</flux:badge>
                </div>

                @if (count($waitingTickets) === 0)
                    <div class="flex flex-col items-center justify-center py-10 px-4 text-center rounded-2xl border border-dashed border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/30">
                        <div class="flex size-14 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400">
                            <flux:icon.check class="size-7" />
                        </div>
                        <p class="mt-3 text-sm font-bold text-zinc-900 dark:text-zinc-100">Antrean Bersih & Terlayani</p>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400 max-w-sm">
                            Semua pemohon di pool ini telah terlayani atau belum ada pengunjung baru yang check-in di kiosk/resepsionis.
                        </p>
                        <div class="mt-3 flex items-center gap-2">
                            <flux:badge size="sm" color="zinc" icon="information-circle">
                                Otomatis sinkron tiap 10 detik
                            </flux:badge>
                        </div>
                    </div>
                @else
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column class="w-16">Urutan</flux:table.column>
                            <flux:table.column>No. Tiket</flux:table.column>
                            <flux:table.column>Layanan</flux:table.column>
                            <flux:table.column>Nama Pengunjung</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach ($waitingTickets as $index => $ticket)
                                <flux:table.row class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <flux:table.cell>
                                        <span class="inline-flex size-6 items-center justify-center rounded-full bg-zinc-100 text-xs font-bold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                            {{ $ticket['sequence_number'] }}
                                        </span>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:badge size="sm" color="cyan" class="font-mono font-bold whitespace-nowrap">{{ $ticket['ticket_number'] }}</flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell class="font-medium text-zinc-900 dark:text-zinc-100 max-w-[200px] truncate" title="{{ $ticket['service_name'] }}">{{ $ticket['service_name'] }}</flux:table.cell>
                                    <flux:table.cell class="text-zinc-600 dark:text-zinc-300 max-w-[200px] truncate" title="{{ $ticket['visitor_name'] ?: 'Pengunjung Walk-in' }}">{{ $ticket['visitor_name'] ?: 'Pengunjung Walk-in' }}</flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                @endif
            </flux:card>

            <!-- Skipped Tickets Tray (Daftar Skip) -->
            <flux:card class="admin-card-elevated space-y-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900">
                <div class="flex items-center justify-between gap-3 border-b border-zinc-100 pb-3 dark:border-zinc-800/80">
                    <div class="flex items-center gap-3">
                        <div class="admin-icon-box bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400">
                            <flux:icon.forward class="size-5" />
                        </div>
                        <div>
                            <flux:heading size="lg" class="font-bold">Daftar Skip</flux:heading>
                            <flux:text class="text-xs text-zinc-500">Tiket terlewati yang dapat dipanggil kembali.</flux:text>
                        </div>
                    </div>
                    <flux:badge color="amber" size="sm" class="font-mono font-bold">{{ count($skippedTickets) }}</flux:badge>
                </div>

                @if (count($skippedTickets) === 0)
                    <div class="flex flex-col items-center justify-center py-6 px-4 text-center rounded-xl border border-dashed border-zinc-200 dark:border-zinc-800 bg-zinc-50/40 dark:bg-zinc-900/30">
                        <flux:icon.check-circle class="size-8 text-zinc-300 dark:text-zinc-600" />
                        <flux:text class="mt-2 text-xs font-semibold text-zinc-700 dark:text-zinc-300">Belum ada tiket terlewati</flux:text>
                        <flux:text class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400 max-w-xs">
                            Jika pemohon belum hadir saat dipanggil, tekan <kbd class="px-1 py-0.5 rounded bg-zinc-200 dark:bg-zinc-800 text-xs font-mono font-bold">F3</kbd> untuk memarkir tiket di sini.
                        </flux:text>
                    </div>
                @else
                    <div class="space-y-2">
                        @foreach ($skippedTickets as $ticket)
                            <div class="flex items-center justify-between gap-2 rounded-xl border border-zinc-200 p-2.5 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/30 transition-colors hover:bg-amber-50/50 dark:hover:bg-amber-950/20">
                                <div class="min-w-0 flex-1">
                                    <flux:badge size="sm" color="amber" class="font-mono font-bold whitespace-nowrap">{{ $ticket['ticket_number'] }}</flux:badge>
                                    <div class="mt-0.5 truncate text-xs text-zinc-500 dark:text-zinc-400" title="{{ $ticket['service_name'] }}">{{ $ticket['service_name'] }}</div>
                                </div>
                                <flux:button 
                                    variant="filled" 
                                    color="cyan" 
                                    size="sm" 
                                    icon="speaker-wave" 
                                    wire:click="restoreSkipped({{ $ticket['id'] }})"
                                    wire:loading.attr="disabled"
                                    class="h-7 px-2.5 text-xs font-semibold shadow-xs shrink-0"
                                    title="Panggil Ulang Tiket Terlewati"
                                >
                                    Panggil
                                </flux:button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </flux:card>
        </div>
    </div>

    <!-- Modals -->
    <flux:modal name="confirm-skip-ticket" class="w-full max-w-md">
        <div class="space-y-4">
            <flux:heading size="lg" class="font-bold">Konfirmasi Lewati Tiket</flux:heading>
            <flux:text>
                Tiket aktif <strong class="font-mono text-amber-600 dark:text-amber-400">{{ $activeTicket?->ticket_number ?? '-' }}</strong> akan dipindahkan ke daftar skip. Anda dapat memanggilnya kembali kapan saja dari panel daftar skip.
            </flux:text>
            <div class="flex justify-end gap-2 pt-2">
                <flux:button type="button" variant="ghost" x-on:click="Flux.modal('confirm-skip-ticket').close()">
                    Batal
                </flux:button>
                <flux:button
                    type="button"
                    variant="filled"
                    color="amber"
                    wire:click="skip"
                    x-on:click="Flux.modal('confirm-skip-ticket').close()"
                    wire:loading.attr="disabled"
                    class="font-bold"
                >
                    Ya, Lewati
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="confirm-cancel-ticket" class="w-full max-w-md">
        <div class="space-y-4">
            <flux:heading size="lg" class="font-bold">Konfirmasi Batalkan Tiket</flux:heading>
            <flux:text>
                Tiket aktif <strong class="font-mono text-red-600 dark:text-red-400">{{ $activeTicket?->ticket_number ?? '-' }}</strong> akan dibatalkan permanen dari antrean hari ini.
            </flux:text>
            <div class="flex justify-end gap-2 pt-2">
                <flux:button type="button" variant="ghost" x-on:click="Flux.modal('confirm-cancel-ticket').close()">
                    Batal
                </flux:button>
                <flux:button
                    type="button"
                    variant="filled"
                    color="red"
                    wire:click="cancel"
                    x-on:click="Flux.modal('confirm-cancel-ticket').close()"
                    wire:loading.attr="disabled"
                    class="font-bold"
                >
                    Ya, Batalkan
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <!-- Hotkey HUD Guide Modal -->
    <div 
        x-cloak
        x-show="showHotkeysModal" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/60 backdrop-blur-xs p-4"
        x-on:click.self="showHotkeysModal = false"
        role="dialog"
        aria-modal="true"
        aria-labelledby="hotkeys-modal-title"
    >
        <div class="w-full max-w-lg rounded-3xl border border-zinc-200/90 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 shadow-2xl shadow-zinc-950/15 dark:shadow-black/60 text-zinc-900 dark:text-zinc-100 space-y-5">
            <div class="flex items-center justify-between border-b border-zinc-200/80 dark:border-zinc-800 pb-3.5">
                <div class="flex items-center gap-3">
                    <div class="flex size-9 items-center justify-center rounded-xl bg-cyan-50 dark:bg-cyan-950/60 border border-cyan-200/80 dark:border-cyan-800/60 text-cyan-700 dark:text-cyan-400 shadow-2xs">
                        <flux:icon.command-line class="size-5" />
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.16em] text-cyan-700 dark:text-cyan-400">Pintasan Cepat</div>
                        <flux:heading id="hotkeys-modal-title" size="lg" class="text-zinc-900 dark:text-white font-bold">Panduan Pintasan Keyboard</flux:heading>
                    </div>
                </div>
                <button 
                    x-on:click="showHotkeysModal = false" 
                    class="rounded-xl p-1.5 text-zinc-400 hover:text-zinc-700 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors" 
                    title="Tutup Panduan"
                    aria-label="Tutup Panduan"
                >
                    <flux:icon.x-mark class="size-5" />
                </button>
            </div>

            <div class="space-y-2.5 text-sm">
                <div class="flex items-center justify-between p-3 rounded-2xl bg-zinc-50/90 dark:bg-zinc-800/60 border border-zinc-200/80 dark:border-zinc-700/60 transition-colors hover:border-cyan-300 dark:hover:border-cyan-800">
                    <div>
                        <div class="font-bold text-zinc-900 dark:text-white text-sm">Panggil Antrean Berikutnya</div>
                        <div class="text-xs text-zinc-600 dark:text-zinc-400 mt-0.5">Memanggil tiket terdepan di pool loket aktif</div>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <kbd class="px-2.5 py-1 rounded-lg bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 font-mono text-xs font-bold text-cyan-700 dark:text-cyan-300 shadow-2xs">Space</kbd>
                        <span class="text-xs text-zinc-400 dark:text-zinc-500 font-medium">atau</span>
                        <kbd class="px-2.5 py-1 rounded-lg bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 font-mono text-xs font-bold text-cyan-700 dark:text-cyan-300 shadow-2xs">F2</kbd>
                    </div>
                </div>

                <div class="flex items-center justify-between p-3 rounded-2xl bg-zinc-50/90 dark:bg-zinc-800/60 border border-zinc-200/80 dark:border-zinc-700/60 transition-colors hover:border-sky-300 dark:hover:border-sky-800">
                    <div>
                        <div class="font-bold text-zinc-900 dark:text-white text-sm">Panggil Ulang (Recall)</div>
                        <div class="text-xs text-zinc-600 dark:text-zinc-400 mt-0.5">Memanggil ulang tiket yang sedang aktif</div>
                    </div>
                    <kbd class="px-2.5 py-1 rounded-lg bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 font-mono text-xs font-bold text-sky-700 dark:text-sky-300 shadow-2xs">F1</kbd>
                </div>

                <div class="flex items-center justify-between p-3 rounded-2xl bg-zinc-50/90 dark:bg-zinc-800/60 border border-zinc-200/80 dark:border-zinc-700/60 transition-colors hover:border-amber-300 dark:hover:border-amber-800">
                    <div>
                        <div class="font-bold text-zinc-900 dark:text-white text-sm">Lewati Tiket (Skip)</div>
                        <div class="text-xs text-zinc-600 dark:text-zinc-400 mt-0.5">Membuka dialog konfirmasi untuk skip tiket</div>
                    </div>
                    <kbd class="px-2.5 py-1 rounded-lg bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 font-mono text-xs font-bold text-amber-700 dark:text-amber-300 shadow-2xs">F3</kbd>
                </div>

                <div class="flex items-center justify-between p-3 rounded-2xl bg-zinc-50/90 dark:bg-zinc-800/60 border border-zinc-200/80 dark:border-zinc-700/60 transition-colors hover:border-emerald-300 dark:hover:border-emerald-800">
                    <div>
                        <div class="font-bold text-zinc-900 dark:text-white text-sm">Selesai Dilayani (Complete)</div>
                        <div class="text-xs text-zinc-600 dark:text-zinc-400 mt-0.5">Menandai pelayanan tiket selesai</div>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <kbd class="px-2.5 py-1 rounded-lg bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 font-mono text-xs font-bold text-emerald-700 dark:text-emerald-300 shadow-2xs">F4</kbd>
                        <span class="text-xs text-zinc-400 dark:text-zinc-500 font-medium">atau</span>
                        <kbd class="px-2.5 py-1 rounded-lg bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 font-mono text-xs font-bold text-emerald-700 dark:text-emerald-300 shadow-2xs">Ctrl+Enter</kbd>
                    </div>
                </div>

                <div class="flex items-center justify-between p-3 rounded-2xl bg-zinc-50/90 dark:bg-zinc-800/60 border border-zinc-200/80 dark:border-zinc-700/60 transition-colors hover:border-rose-300 dark:hover:border-rose-800">
                    <div>
                        <div class="font-bold text-zinc-900 dark:text-white text-sm">Batalkan Tiket</div>
                        <div class="text-xs text-zinc-600 dark:text-zinc-400 mt-0.5">Membuka dialog konfirmasi pembatalan tiket</div>
                    </div>
                    <kbd class="px-2.5 py-1 rounded-lg bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 font-mono text-xs font-bold text-rose-700 dark:text-rose-300 shadow-2xs">F8</kbd>
                </div>

                <div class="flex items-center justify-between p-3 rounded-2xl bg-zinc-50/90 dark:bg-zinc-800/60 border border-zinc-200/80 dark:border-zinc-700/60">
                    <div>
                        <div class="font-bold text-zinc-900 dark:text-white text-sm">Buka Panduan Ini</div>
                        <div class="text-xs text-zinc-600 dark:text-zinc-400 mt-0.5">Menampilkan atau menutup popup hotkeys</div>
                    </div>
                    <kbd class="px-2.5 py-1 rounded-lg bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 font-mono text-xs font-bold text-zinc-700 dark:text-zinc-200 shadow-2xs">?</kbd>
                </div>
            </div>

            <div class="pt-2 flex items-center justify-between border-t border-zinc-200/80 dark:border-zinc-800">
                <span class="text-xs text-zinc-500 dark:text-zinc-400">Tekan <kbd class="px-1.5 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 font-mono text-xs font-semibold text-zinc-700 dark:text-zinc-300">Esc</kbd> untuk menutup</span>
                <flux:button variant="filled" color="cyan" x-on:click="showHotkeysModal = false" class="font-bold shadow-xs">
                    Tutup Panduan
                </flux:button>
            </div>
        </div>
    </div>

    <!-- Interactive Workstation Onboarding Modal -->
    <div 
        x-cloak
        x-show="showOnboardingModal" 
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/65 backdrop-blur-xs p-4"
        x-on:click.self="completeOnboarding()"
        role="dialog"
        aria-modal="true"
        aria-labelledby="onboarding-modal-title"
    >
        <div class="w-full max-w-xl rounded-3xl border border-zinc-200/90 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-6 sm:p-7 shadow-2xl shadow-zinc-950/15 dark:shadow-black/60 text-zinc-900 dark:text-zinc-100 space-y-6">
            <!-- Header & Step Indicator -->
            <div class="flex items-center justify-between border-b border-zinc-200/80 dark:border-zinc-800 pb-4">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-2xl bg-cyan-100 dark:bg-cyan-500/20 border border-cyan-200/80 dark:border-cyan-500/30 text-cyan-700 dark:text-cyan-400 shadow-2xs">
                        <flux:icon.sparkles class="size-5" />
                    </div>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.16em] text-cyan-700 dark:text-cyan-400">Panduan Operasional Loket</div>
                        <flux:heading id="onboarding-modal-title" size="lg" class="text-zinc-900 dark:text-white font-extrabold text-lg sm:text-xl">Tur Cepat Workstation Petugas</flux:heading>
                    </div>
                </div>
                <button 
                    x-on:click="completeOnboarding()" 
                    class="rounded-xl p-1.5 text-zinc-400 hover:text-zinc-700 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors" 
                    title="Tutup Panduan"
                    aria-label="Tutup Panduan"
                >
                    <flux:icon.x-mark class="size-5" />
                </button>
            </div>

            <!-- Step Progress Indicator -->
            <div class="space-y-2">
                <div class="flex items-center justify-between text-xs font-semibold text-zinc-600 dark:text-zinc-400">
                    <span>Langkah <strong class="text-cyan-700 dark:text-cyan-400 text-sm" x-text="onboardingStep"></strong> dari 4</span>
                    <span class="text-zinc-700 dark:text-zinc-300 font-bold" x-show="onboardingStep === 1">1. Pemilihan Loket & Pool</span>
                    <span class="text-zinc-700 dark:text-zinc-300 font-bold" x-show="onboardingStep === 2">2. Panggilan & Stopwatch</span>
                    <span class="text-zinc-700 dark:text-zinc-300 font-bold" x-show="onboardingStep === 3">3. Panggil Ulang & Skip</span>
                    <span class="text-zinc-700 dark:text-zinc-300 font-bold" x-show="onboardingStep === 4">4. Penyelesaian & Kinerja</span>
                </div>
                <div class="grid grid-cols-4 gap-2">
                    <div class="h-1.5 rounded-full transition-all duration-300" :class="onboardingStep >= 1 ? 'bg-cyan-600 dark:bg-cyan-500 shadow-2xs' : 'bg-zinc-200 dark:bg-zinc-800'"></div>
                    <div class="h-1.5 rounded-full transition-all duration-300" :class="onboardingStep >= 2 ? 'bg-cyan-600 dark:bg-cyan-500 shadow-2xs' : 'bg-zinc-200 dark:bg-zinc-800'"></div>
                    <div class="h-1.5 rounded-full transition-all duration-300" :class="onboardingStep >= 3 ? 'bg-cyan-600 dark:bg-cyan-500 shadow-2xs' : 'bg-zinc-200 dark:bg-zinc-800'"></div>
                    <div class="h-1.5 rounded-full transition-all duration-300" :class="onboardingStep >= 4 ? 'bg-cyan-600 dark:bg-cyan-500 shadow-2xs' : 'bg-zinc-200 dark:bg-zinc-800'"></div>
                </div>
            </div>

            <!-- Dynamic Step Content Body -->
            <div class="min-h-[220px]">
                <!-- Step 1: Counter Selection -->
                <div x-show="onboardingStep === 1" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-2" x-transition:enter-end="opacity-100 translate-x-0" class="space-y-4">
                    <div class="rounded-2xl border border-cyan-200/90 dark:border-cyan-500/30 bg-gradient-to-br from-cyan-50/90 to-sky-50/50 dark:from-cyan-950/40 dark:to-zinc-900/40 p-4.5 flex items-start gap-3.5 shadow-2xs">
                        <div class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-cyan-600 text-white dark:bg-cyan-500/20 dark:text-cyan-400 shadow-xs">
                            <flux:icon.building-office class="size-6" />
                        </div>
                        <div class="space-y-1.5">
                            <h4 class="text-sm font-bold text-zinc-900 dark:text-white">1. Pilih & Kunci Loket Shift Anda</h4>
                            <p class="text-xs text-zinc-700 dark:text-zinc-300 leading-relaxed">
                                Workstation ini terhubung dengan <strong>Pool Antrean</strong> pelayanan PTSP. Pastikan Anda memilih loket yang tepat sebelum mulai bertugas agar antrean pemohon diarahkan ke loket Anda.
                            </p>
                        </div>
                    </div>
                    <div class="rounded-2xl bg-zinc-50 dark:bg-zinc-800/60 p-3.5 text-xs text-zinc-700 dark:text-zinc-300 border border-zinc-200/80 dark:border-zinc-700/60 flex items-center justify-between shadow-2xs">
                        <span class="font-medium">Loket yang saat ini aktif:</span>
                        <flux:badge color="cyan" size="sm" class="font-bold">{{ $this->selectedCounterName }}</flux:badge>
                    </div>
                </div>

                <!-- Step 2: Calling & Live Stopwatch -->
                <div x-show="onboardingStep === 2" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-2" x-transition:enter-end="opacity-100 translate-x-0" class="space-y-4">
                    <div class="rounded-2xl border border-emerald-200/90 dark:border-emerald-500/30 bg-gradient-to-br from-emerald-50/90 to-teal-50/50 dark:from-emerald-950/40 dark:to-zinc-900/40 p-4.5 flex items-start gap-3.5 shadow-2xs">
                        <div class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-600 text-white dark:bg-emerald-500/20 dark:text-emerald-400 shadow-xs">
                            <flux:icon.megaphone class="size-6" />
                        </div>
                        <div class="space-y-1.5">
                            <h4 class="text-sm font-bold text-zinc-900 dark:text-white">2. Panggil Antrean & Pantau Stopwatch</h4>
                            <p class="text-xs text-zinc-700 dark:text-zinc-300 leading-relaxed">
                                Tekan tombol <strong>Panggil Berikutnya</strong> atau tekan tombol <kbd class="px-1.5 py-0.5 rounded-lg bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 font-mono text-xs font-bold text-cyan-700 dark:text-cyan-300 shadow-2xs">Space</kbd> / <kbd class="px-1.5 py-0.5 rounded-lg bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 font-mono text-xs font-bold text-cyan-700 dark:text-cyan-300 shadow-2xs">F2</kbd>. Suara panggilan otomatis berbunyi dan stopwatch durasi live mulai menghitung.
                            </p>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2.5 text-center text-xs">
                        <div class="rounded-xl border border-emerald-200/90 dark:border-emerald-500/40 bg-emerald-50/90 dark:bg-emerald-950/50 p-2.5 text-emerald-800 dark:text-emerald-200 font-bold shadow-2xs">
                            &lt; 10m: Normal
                        </div>
                        <div class="rounded-xl border border-amber-200/90 dark:border-amber-500/40 bg-amber-50/90 dark:bg-amber-950/50 p-2.5 text-amber-800 dark:text-amber-200 font-bold shadow-2xs">
                            10-20m: Sedang
                        </div>
                        <div class="rounded-xl border border-rose-200/90 dark:border-rose-500/40 bg-rose-50/90 dark:bg-rose-950/50 p-2.5 text-rose-800 dark:text-rose-200 font-bold shadow-2xs">
                            &gt; 20m: Lama
                        </div>
                    </div>
                </div>

                <!-- Step 3: Recall & Skip Tray -->
                <div x-show="onboardingStep === 3" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-2" x-transition:enter-end="opacity-100 translate-x-0" class="space-y-4">
                    <div class="rounded-2xl border border-amber-200/90 dark:border-amber-500/30 bg-gradient-to-br from-amber-50/90 to-orange-50/50 dark:from-amber-950/40 dark:to-zinc-900/40 p-4.5 flex items-start gap-3.5 shadow-2xs">
                        <div class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-amber-600 text-white dark:bg-amber-500/20 dark:text-amber-400 shadow-xs">
                            <flux:icon.forward class="size-6" />
                        </div>
                        <div class="space-y-1.5">
                            <h4 class="text-sm font-bold text-zinc-900 dark:text-white">3. Panggil Ulang (F1) & Lewati/Skip (F3)</h4>
                            <p class="text-xs text-zinc-700 dark:text-zinc-300 leading-relaxed">
                                Bila pemohon belum mendekat, panggil ulang dengan tombol <kbd class="px-1.5 py-0.5 rounded-lg bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 font-mono text-xs font-bold text-sky-700 dark:text-sky-300 shadow-2xs">F1</kbd>. Jika tetap tidak hadir, tekan <kbd class="px-1.5 py-0.5 rounded-lg bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 font-mono text-xs font-bold text-amber-700 dark:text-amber-300 shadow-2xs">F3</kbd> (Lewati) untuk memarkir tiket ke <strong>Daftar Skip</strong> dan lanjut melayani antrean berikutnya.
                            </p>
                        </div>
                    </div>
                    <div class="rounded-2xl bg-zinc-50 dark:bg-zinc-800/60 p-3.5 text-xs text-zinc-700 dark:text-zinc-300 border border-zinc-200/80 dark:border-zinc-700/60 flex items-center justify-between shadow-2xs">
                        <span class="font-medium">Tiket di Daftar Skip dapat dipanggil kembali:</span>
                        <flux:badge color="amber" size="sm" class="font-bold">1-Klik Panggil</flux:badge>
                    </div>
                </div>

                <!-- Step 4: Complete & Daily Stats -->
                <div x-show="onboardingStep === 4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-2" x-transition:enter-end="opacity-100 translate-x-0" class="space-y-4">
                    <div class="rounded-2xl border border-emerald-200/90 dark:border-emerald-500/30 bg-gradient-to-br from-emerald-50/90 to-teal-50/50 dark:from-emerald-950/40 dark:to-zinc-900/40 p-4.5 flex items-start gap-3.5 shadow-2xs">
                        <div class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-600 text-white dark:bg-emerald-500/20 dark:text-emerald-400 shadow-xs">
                            <flux:icon.check-circle class="size-6" />
                        </div>
                        <div class="space-y-1.5">
                            <h4 class="text-sm font-bold text-zinc-900 dark:text-white">4. Tuntaskan Layanan & Pantau Kinerja</h4>
                            <p class="text-xs text-zinc-700 dark:text-zinc-300 leading-relaxed">
                                Begitu pelayanan selesai, tekan tombol <kbd class="px-1.5 py-0.5 rounded-lg bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 font-mono text-xs font-bold text-emerald-700 dark:text-emerald-300 shadow-2xs">F4</kbd> atau <kbd class="px-1.5 py-0.5 rounded-lg bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 font-mono text-xs font-bold text-emerald-700 dark:text-emerald-300 shadow-2xs">Ctrl+Enter</kbd>. Statistik harian Anda akan otomatis tercatat dan loket kembali siap memanggil pemohon baru.
                            </p>
                        </div>
                    </div>
                    <div class="rounded-2xl bg-emerald-50/90 dark:bg-emerald-950/40 p-3.5 text-xs text-emerald-900 dark:text-emerald-200 border border-emerald-200/90 dark:border-emerald-500/30 flex items-center justify-between shadow-2xs">
                        <span class="font-semibold">Buka panduan pintasan kapan saja dengan tombol:</span>
                        <kbd class="px-2.5 py-0.5 rounded-lg bg-white dark:bg-zinc-900 border border-emerald-300 dark:border-emerald-500/50 font-mono text-xs font-bold text-emerald-800 dark:text-emerald-200 shadow-2xs">?</kbd>
                    </div>
                </div>
            </div>

            <!-- Modal Action Footer -->
            <div class="flex items-center justify-between border-t border-zinc-200/80 dark:border-zinc-800 pt-4">
                <flux:button 
                    variant="ghost" 
                    size="sm" 
                    x-show="onboardingStep > 1" 
                    x-on:click="prevOnboardingStep()"
                    class="text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white"
                >
                    <flux:icon.arrow-left class="size-4 mr-1" />
                    <span>Kembali</span>
                </flux:button>
                <div x-show="onboardingStep === 1">
                    <button x-on:click="completeOnboarding()" class="text-xs text-zinc-500 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200 underline font-medium">
                        Lewati Panduan
                    </button>
                </div>

                <div class="flex items-center gap-2 ml-auto">
                    <flux:button 
                        variant="filled" 
                        color="cyan" 
                        size="sm" 
                        x-on:click="nextOnboardingStep()"
                        class="font-bold shadow-md shadow-cyan-600/20"
                    >
                        <span x-text="onboardingStep === 4 ? 'Mulai Bertugas Sekarang' : 'Lanjut'"></span>
                        <flux:icon.arrow-right class="size-4 ml-1" />
                    </flux:button>
                </div>
            </div>
        </div>
    </div>
</div>
