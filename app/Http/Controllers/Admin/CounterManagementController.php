<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCounterRequest;
use App\Models\Counter;
use App\Models\QueuePool;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CounterManagementController extends Controller
{
    public function index(): View
    {
        $counters = Counter::query()
            ->with('queuePool')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $queuePools = QueuePool::query()
            ->orderBy('name')
            ->get();

        return view('pages.admin.loket.index', [
            'counters' => $counters,
            'queuePools' => $queuePools,
        ]);
    }

    public function update(UpdateCounterRequest $request, Counter $counter): RedirectResponse
    {
        $counter->update($request->validated());

        return redirect('/admin/loket')
            ->with('status', 'Loket Berhasil Diperbarui');
    }
}
