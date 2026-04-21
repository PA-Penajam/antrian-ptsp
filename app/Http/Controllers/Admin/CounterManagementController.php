<?php

namespace App\Http\Controllers\Admin;

use App\Enums\QueueStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCounterRequest;
use App\Http\Requests\StorePoolRequest;
use App\Http\Requests\UpdateCounterRequest;
use App\Http\Requests\UpdatePoolRequest;
use App\Models\Counter;
use App\Models\QueuePool;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CounterManagementController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $sortBy = $request->query('sort_by', 'sort_order');
        $sortDirection = $request->query('sort_direction', 'asc');

        $allowedSortColumns = ['name', 'code', 'sort_order', 'is_active'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'sort_order';
        }
        $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'asc';

        $counters = Counter::query()
            ->with('queuePool')
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
            ->orderBy($sortBy, $sortDirection)
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $queuePools = QueuePool::query()
            ->orderBy('name')
            ->get();

        return view('pages.admin.loket.index', [
            'counters' => $counters,
            'queuePools' => $queuePools,
            'sortBy' => $sortBy,
            'sortDirection' => $sortDirection,
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
        // Check for active tickets (booked, waiting, or called)
        $hasActiveTickets = $counter->queueTickets()
            ->whereIn('status', [QueueStatus::Booked->value, QueueStatus::Waiting->value, QueueStatus::Called->value])
            ->exists();

        if ($hasActiveTickets) {
            return redirect()->route('admin.loket.index')
                ->with('error', 'Loket tidak dapat dihapus karena memiliki antrian aktif.');
        }

        // Check for active counter sessions
        $hasActiveSessions = $counter->sessions()
            ->where('status', 'open')
            ->exists();

        if ($hasActiveSessions) {
            return redirect()->route('admin.loket.index')
                ->with('error', 'Loket tidak dapat dihapus karena memiliki sesi aktif.');
        }

        $counter->delete();

        return redirect()->route('admin.loket.index')
            ->with('status', 'Loket berhasil dihapus.');
    }

    public function storePool(StorePoolRequest $request): RedirectResponse
    {
        QueuePool::query()->create($request->validated());

        return redirect()->route('admin.loket.index')
            ->with('status', 'Pool antrian berhasil dibuat.');
    }

    public function updatePool(UpdatePoolRequest $request, QueuePool $pool): RedirectResponse
    {
        $pool->update($request->validated());

        return redirect()->route('admin.loket.index')
            ->with('status', 'Pool antrian berhasil diperbarui.');
    }

    public function destroyPool(QueuePool $pool): RedirectResponse
    {
        $hasServices = $pool->services()->exists();
        $hasCounters = $pool->counters()->exists();
        $hasTickets = $pool->queueTickets()->exists();

        if ($hasServices || $hasCounters || $hasTickets) {
            return redirect()->route('admin.loket.index')
                ->with('error', 'Pool tidak dapat dihapus karena masih terhubung dengan layanan, loket, atau antrian.');
        }

        $pool->delete();

        return redirect()->route('admin.loket.index')
            ->with('status', 'Pool antrian berhasil dihapus.');
    }
}
