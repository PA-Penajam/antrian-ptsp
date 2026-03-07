<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\QueuePool;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceManagementController extends Controller
{
    public function index(): View
    {
        $services = Service::query()
            ->with('queuePool')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

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

        return redirect('/admin/layanan')
            ->with('status', 'Layanan Berhasil Dibuat');
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $service->update($request->validated());

        return redirect('/admin/layanan')
            ->with('status', 'Layanan Berhasil Diperbarui');
    }
}
