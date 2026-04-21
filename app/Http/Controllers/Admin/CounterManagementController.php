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
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class CounterManagementController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $sortBy = $request->query('sort_by', 'sort_order');
        $sortDirection = $request->query('sort_direction', 'asc');

        $allowedSortColumns = ['name', 'code', 'sort_order', 'is_active'];
        if (! in_array($sortBy, $allowedSortColumns)) {
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
        try {
            $counter->update($request->validated());

            return redirect()->route('admin.loket.index')
                ->with('status', 'Loket berhasil diperbarui.');
        } catch (Throwable $e) {
            Log::error('[Admin][Loket] Gagal memperbarui loket', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'counter_id' => $counter->id,
                'input' => $request->except(['_token', '_method']),
            ]);

            return redirect()->route('admin.loket.index')
                ->with('error', 'Terjadi kesalahan saat memperbarui loket. Silakan coba lagi.');
        }
    }

    public function store(StoreCounterRequest $request): RedirectResponse
    {
        try {
            Counter::query()->create($request->validated());

            return redirect()->route('admin.loket.index')
                ->with('status', 'Loket berhasil dibuat.');
        } catch (Throwable $e) {
            Log::error('[Admin][Loket] Gagal membuat loket', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'input' => $request->except(['_token']),
            ]);

            return redirect()->route('admin.loket.index')
                ->with('error', 'Terjadi kesalahan saat membuat loket. Silakan coba lagi.');
        }
    }

    public function destroy(Counter $counter): RedirectResponse
    {
        try {
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
        } catch (Throwable $e) {
            Log::error('[Admin][Loket] Gagal menghapus loket', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'counter_id' => $counter->id,
            ]);

            return redirect()->route('admin.loket.index')
                ->with('error', 'Terjadi kesalahan saat menghapus loket. Silakan coba lagi.');
        }
    }

    public function storePool(StorePoolRequest $request): RedirectResponse
    {
        try {
            QueuePool::query()->create($request->validated());

            return redirect()->route('admin.loket.index')
                ->with('status', 'Pool antrian berhasil dibuat.');
        } catch (Throwable $e) {
            Log::error('[Admin][Loket][Pool] Gagal membuat pool', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'input' => $request->except(['_token']),
            ]);

            return redirect()->route('admin.loket.index')
                ->with('error', 'Terjadi kesalahan saat membuat pool. Silakan coba lagi.');
        }
    }

    public function updatePool(UpdatePoolRequest $request, QueuePool $pool): RedirectResponse
    {
        try {
            $pool->update($request->validated());

            return redirect()->route('admin.loket.index')
                ->with('status', 'Pool antrian berhasil diperbarui.');
        } catch (Throwable $e) {
            Log::error('[Admin][Loket][Pool] Gagal memperbarui pool', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'pool_id' => $pool->id,
                'input' => $request->except(['_token', '_method']),
            ]);

            return redirect()->route('admin.loket.index')
                ->with('error', 'Terjadi kesalahan saat memperbarui pool. Silakan coba lagi.');
        }
    }

    public function destroyPool(QueuePool $pool): RedirectResponse
    {
        try {
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
        } catch (Throwable $e) {
            Log::error('[Admin][Loket][Pool] Gagal menghapus pool', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'pool_id' => $pool->id,
            ]);

            return redirect()->route('admin.loket.index')
                ->with('error', 'Terjadi kesalahan saat menghapus pool. Silakan coba lagi.');
        }
    }
}
