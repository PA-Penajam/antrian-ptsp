<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class TimeController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'timestamp' => now()->getTimestampMs(),
            'datetime' => now()->format('Y-m-d H:i:s'),
            'timezone' => config('app.timezone'),
        ]);
    }
}
