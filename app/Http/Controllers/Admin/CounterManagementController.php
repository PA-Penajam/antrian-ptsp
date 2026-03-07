<?php

namespace App\Http\Controllers\Admin;

use App\Enums\QueueStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCounterRequest;
use App\Http\Requests\UpdateCounterRequest;
use App\Models\Counter;
use App\Models\QueuePool;
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

        return view('pages.admin.loket.index', [
            'counters' => $counters,
            'queuePools' => $queuePools,
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
}
