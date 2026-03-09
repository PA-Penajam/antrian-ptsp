<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PublicServiceController extends Controller
{
    public function institution(): JsonResponse
    {
        return response()->json(config('institution'));
    }

    public function index(): AnonymousResourceCollection
    {
        $services = Service::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return ServiceResource::collection($services);
    }

    public function show(string $slug): ServiceResource
    {
        $service = Service::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return ServiceResource::make($service);
    }
}
