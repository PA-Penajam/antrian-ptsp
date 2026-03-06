<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Http\Requests\QueueReportFilterRequest;
use App\Support\Reports\QueueReportBuilder;
use Illuminate\Contracts\View\View;

class QueueReportController extends Controller
{
    public function index(QueueReportFilterRequest $request, QueueReportBuilder $queueReportBuilder): View
    {
        $validated = $request->validated();
        $from = $validated['from'] ?? now()->toDateString();
        $to = $validated['to'] ?? now()->toDateString();

        $report = $queueReportBuilder->build($from, $to);

        return view('pages.laporan.antrian.index', [
            'from' => $from,
            'to' => $to,
            'report' => $report,
        ]);
    }
}
