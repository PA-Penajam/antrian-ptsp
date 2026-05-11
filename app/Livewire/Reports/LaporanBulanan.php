<?php

namespace App\Livewire\Reports;

use App\Exports\LaporanBulananExport;
use App\Support\Reports\LaporanBulananReportBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Title('Laporan Bulanan Pendaftar Layanan')]
class LaporanBulanan extends Component
{
    public int $tahun;

    public int $bulan;

    protected function rules(): array
    {
        return [
            'bulan' => ['required', 'integer', 'min:1', 'max:12'],
            'tahun' => ['required', 'integer', 'min:2020', 'max:2099'],
        ];
    }

    public function mount(): void
    {
        $this->tahun = (int) now()->format('Y');
        $this->bulan = (int) now()->format('n');
    }

    public function downloadExcel(): BinaryFileResponse
    {
        $this->validate();

        $report = app(LaporanBulananReportBuilder::class)->build($this->bulan, $this->tahun);

        return Excel::download(
            new LaporanBulananExport($report),
            'Laporan_Bulanan_'.str($report['judul_bulan'])->replace(' ', '_').'.xlsx'
        );
    }

    public function downloadPdf(): Response
    {
        $this->validate();

        $report = app(LaporanBulananReportBuilder::class)->build($this->bulan, $this->tahun);

        $pdf = Pdf::loadView('pdf.laporan-bulanan', [
            'judulBulan' => $report['judul_bulan'],
            'ringkasan' => $report['ringkasan'],
            'perLayanan' => $report['per_layanan'],
            'perHari' => $report['per_hari'],
            'perChannel' => $report['per_channel'],
        ]);

        return $pdf->download('Laporan_Bulanan_'.str($report['judul_bulan'])->replace(' ', '_').'.pdf');
    }

    public function render(): View
    {
        $this->validate();

        $report = app(LaporanBulananReportBuilder::class)->build($this->bulan, $this->tahun);

        return view('livewire.reports.laporan-bulanan', [
            'report' => $report,
        ]);
    }
}
