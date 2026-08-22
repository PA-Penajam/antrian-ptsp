<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PerChannelSheet implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    /**
     * Data per channel dari report builder.
     *
     * @var array<int, array{channel: string, total: int, persen: float}>
     */
    protected array $data;

    /**
     * @param  array<int, array{channel: string, total: int, persen: float}>  $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Mengembalikan baris data untuk sheet Per Kanal.
     *
     * @return array<int, array<int, string|int|float>>
     */
    public function array(): array
    {
        return array_map(fn (array $item): array => [
            $item['channel'],
            $item['total'],
            $item['persen'].'%',
        ], $this->data);
    }

    /**
     * Mengembalikan header kolom.
     *
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Kanal',
            'Total',
            'Persentase',
        ];
    }

    /**
     * Styling untuk worksheet Excel.
     *
     * @return array<int|string, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * Judul sheet.
     */
    public function title(): string
    {
        return 'Per Kanal';
    }
}
