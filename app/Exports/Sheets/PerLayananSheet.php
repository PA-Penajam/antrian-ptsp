<?php

namespace App\Exports\Sheets;

use App\Exports\Concerns\SanitizesCellValues;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PerLayananSheet implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    use SanitizesCellValues;

    /**
     * Data per layanan dari report builder.
     *
     * @var array<int, array{id: int, name: string, total: int, completed: int, cancelled: int}>
     */
    protected array $data;

    /**
     * @param  array<int, array{id: int, name: string, total: int, completed: int, cancelled: int}>  $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return array_map(fn (array $item): array => [
            $this->sanitizeCellValue($item['name']),
            $item['total'],
            $item['completed'],
            $item['cancelled'],
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
            'Layanan',
            'Total',
            'Selesai',
            'Dibatalkan',
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
        return 'Per Layanan';
    }
}
