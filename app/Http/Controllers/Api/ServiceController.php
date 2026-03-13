<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;

class ServiceController extends Controller
{
    /**
     * Display a listing of active services.
     */
    public function index()
    {
        $services = Service::active()->get();

        return ServiceResource::collection($services);
    }

    /**
     * Display the specified service by slug.
     */
    public function show(string $slug)
    {
        $service = Service::active()
            ->where('slug', $slug)
            ->firstOrFail();

        return new ServiceResource($service);
    }
}
