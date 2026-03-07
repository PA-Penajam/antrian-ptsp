<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCounterRequest;
use App\Models\Counter;
use Illuminate\Http\Response;

class CounterManagementController extends Controller
{
    public function index(): Response
    {
        $counters = Counter::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $lines = ['Manajemen Loket'];
        foreach ($counters as $counter) {
            $lines[] = $counter->name;
        }

        return response(implode("\n", $lines), 200);
    }

    public function update(UpdateCounterRequest $request, Counter $counter): Response
    {
        $counter->update($request->validated());

        return response('Loket Berhasil Diperbarui', 200);
    }
}
