<?php

namespace App\Exports;

use App\Exports\Sheets\PerChannelSheet;
use App\Exports\Sheets\PerHariSheet;
use App\Exports\Sheets\PerLayananSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanBulananExport implements WithMultipleSheets
{
    use Exportable;

    /**
     * Dataset laporan dari LaporanBulananReportBuilder.
     *
     * @var array{judul_bulan: string, ringkasan: array, per_layanan: array, per_hari: array, per_channel: array}
     */
    protected array $report;

    /**
     * @param  array{judul_bulan: string, ringkasan: array, per_layanan: array, per_hari: array, per_channel: array}  $report
     */
    public function __construct(array $report)
    {
        $this->report = $report;
    }

    /**
     * Mengembalikan array sheet untuk multi-sheet Excel.
     *
     * @return array<int, object>
     */
    public function sheets(): array
    {
        return [
            new PerLayananSheet($this->report['per_layanan']),
            new PerHariSheet($this->report['per_hari']),
            new PerChannelSheet($this->report['per_channel']),
        ];
    }
}
