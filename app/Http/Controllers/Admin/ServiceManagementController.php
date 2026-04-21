<?php

namespace App\Http\Controllers\Admin;

use App\Enums\QueueStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\QueuePool;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class ServiceManagementController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $sortBy = $request->query('sort_by', 'sort_order');
        $sortDirection = $request->query('sort_direction', 'asc');

        $allowedSortColumns = ['name', 'code', 'is_active', 'sort_order'];
        if (! in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'sort_order';
        }
        $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'asc';

        $services = Service::query()
            ->with('queuePool')
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
            ->orderBy($sortBy, $sortDirection)
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $queuePools = QueuePool::query()
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get();

        return view('pages.admin.layanan.index', [
            'services' => $services,
            'queuePools' => $queuePools,
            'sortBy' => $sortBy,
            'sortDirection' => $sortDirection,
        ]);
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        try {
            Service::query()->create($request->validated());

            return redirect()->route('admin.layanan.index')
                ->with('status', 'Layanan Berhasil Dibuat');
        } catch (Throwable $e) {
            Log::error('[Admin][Layanan] Gagal membuat layanan', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'input' => $request->except(['_token']),
            ]);

            return redirect()->route('admin.layanan.index')
                ->with('error', 'Terjadi kesalahan saat membuat layanan. Silakan coba lagi.');
        }
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        try {
            $service->update($request->validated());

            return redirect()->route('admin.layanan.index')
                ->with('status', 'Layanan Berhasil Diperbarui');
        } catch (Throwable $e) {
            Log::error('[Admin][Layanan] Gagal memperbarui layanan', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'service_id' => $service->id,
                'input' => $request->except(['_token', '_method']),
            ]);

            return redirect()->route('admin.layanan.index')
                ->with('error', 'Terjadi kesalahan saat memperbarui layanan. Silakan coba lagi.');
        }
    }

    public function destroy(Service $service): RedirectResponse
    {
        try {
            $hasActiveTickets = $service->queueTickets()
                ->whereIn('status', [QueueStatus::Booked->value, QueueStatus::Waiting->value, QueueStatus::Called->value])
                ->exists();

            if ($hasActiveTickets) {
                return redirect()->route('admin.layanan.index')
                    ->with('error', 'Layanan tidak dapat dihapus karena memiliki antrian aktif.');
            }

            // Delete all non-active tickets first to satisfy foreign key constraint
            $service->queueTickets()->delete();

            $service->delete();

            return redirect()->route('admin.layanan.index')
                ->with('status', 'Layanan berhasil dihapus.');
        } catch (Throwable $e) {
            Log::error('[Admin][Layanan] Gagal menghapus layanan', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'service_id' => $service->id,
            ]);

            return redirect()->route('admin.layanan.index')
                ->with('error', 'Terjadi kesalahan saat menghapus layanan. Silakan coba lagi.');
        }
    }
}
