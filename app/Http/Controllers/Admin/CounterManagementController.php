<?php

namespace App\Http\Controllers\Admin;

use App\Enums\QueueStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignCounterOfficerRequest;
use App\Http\Requests\StoreCounterRequest;
use App\Http\Requests\UpdateCounterRequest;
use App\Models\Counter;
use App\Models\CounterSession;
use App\Models\QueuePool;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CounterManagementController extends Controller
{
    public function index(): View
    {
        $counters = Counter::query()
            ->with('queuePool')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $queuePools = QueuePool::query()
            ->orderBy('name')
            ->get();

        $officers = User::query()
            ->where('role', UserRole::Officer)
            ->with('services')
            ->orderBy('name')
            ->get();

        $activeSessions = CounterSession::query()
            ->with(['user', 'assigner'])
            ->where('status', 'open')
            ->whereDate('opened_at', today())
            ->get()
            ->keyBy('counter_id');

        return view('pages.admin.loket.index', [
            'counters' => $counters,
            'queuePools' => $queuePools,
            'officers' => $officers,
            'activeSessions' => $activeSessions,
        ]);
    }

    public function update(UpdateCounterRequest $request, Counter $counter): RedirectResponse
    {
        $counter->update($request->validated());

        return redirect()->route('admin.loket.index')
            ->with('status', 'Loket berhasil diperbarui.');
    }

    public function store(StoreCounterRequest $request): RedirectResponse
    {
        Counter::query()->create($request->validated());

        return redirect()->route('admin.loket.index')
            ->with('status', 'Loket berhasil dibuat.');
    }

    public function destroy(Counter $counter): RedirectResponse
    {
        $hasActiveTickets = $counter->queueTickets()
            ->whereIn('status', [QueueStatus::Waiting, QueueStatus::Called])
            ->exists();

        if ($hasActiveTickets) {
            return redirect()->route('admin.loket.index')
                ->with('error', 'Loket tidak dapat dihapus karena memiliki antrian aktif.');
        }

        $counter->delete();

        return redirect()->route('admin.loket.index')
            ->with('status', 'Loket berhasil dihapus.');
    }

    public function assignOfficer(AssignCounterOfficerRequest $request, Counter $counter): RedirectResponse
    {
        $validated = $request->validated();
        $userId = $validated['user_id'];

        CounterSession::query()
            ->where('user_id', $userId)
            ->where('status', 'open')
            ->update([
                'status' => 'closed',
                'closed_at' => now(),
            ]);

        CounterSession::query()->create([
            'counter_id' => $counter->id,
            'user_id' => $userId,
            'assigned_by' => auth()->id(),
            'opened_at' => now(),
            'status' => 'open',
        ]);

        return redirect()->route('admin.loket.index', ['tab' => 'assignment'])
            ->with('status', 'Petugas berhasil ditugaskan ke loket.');
    }

    public function releaseOfficer(Counter $counter): RedirectResponse
    {
        CounterSession::query()
            ->where('counter_id', $counter->id)
            ->where('status', 'open')
            ->update([
                'status' => 'closed',
                'closed_at' => now(),
            ]);

        return redirect()->route('admin.loket.index', ['tab' => 'assignment'])
            ->with('status', 'Penugasan petugas dilepaskan.');
    }
}
