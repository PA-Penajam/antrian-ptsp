<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Http\Response;

class ServiceManagementController extends Controller
{
    public function index(): Response
    {
        $services = Service::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $lines = ['Manajemen Layanan'];
        foreach ($services as $service) {
            $lines[] = $service->name;
        }

        return response(implode("\n", $lines), 200);
    }

    public function store(StoreServiceRequest $request): Response
    {
        Service::query()->create($request->validated());

        return response('Layanan Berhasil Dibuat', 200);
    }

    public function update(UpdateServiceRequest $request, Service $service): Response
    {
        $service->update($request->validated());

        return response('Layanan Berhasil Diperbarui', 200);
    }
}
