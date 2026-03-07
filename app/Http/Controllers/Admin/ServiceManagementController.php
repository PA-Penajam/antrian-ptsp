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
use Illuminate\View\View;

class ServiceManagementController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $services = Service::query()
            ->with('queuePool')
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
            ->orderBy('sort_order')
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
        ]);
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        Service::query()->create($request->validated());

        return redirect()->route('admin.layanan.index')
            ->with('status', 'Layanan Berhasil Dibuat');
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $service->update($request->validated());

        return redirect()->route('admin.layanan.index')
            ->with('status', 'Layanan Berhasil Diperbarui');
    }

    public function destroy(Service $service): RedirectResponse
    {
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
    }
}
