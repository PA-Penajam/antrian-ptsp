<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class PerLayananSheet implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
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

    /**
     * Mengembalikan baris data untuk sheet Per Layanan.
     *
     * @return array<int, array<int, string|int>>
     */
    public function array(): array
    {
        return array_map(fn (array $item): array => [
            $item['name'],
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
     * Judul sheet.
     */
    public function title(): string
    {
        return 'Per Layanan';
    }
}
