<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class PerHariSheet implements FromArray, ShouldAutoSize, WithHeadings, WithTitle
{
    /**
     * Data per hari dari report builder.
     *
     * @var array<int, array{date: string, nama_hari: string, total: int, online: int, kiosk: int, assisted: int}>
     */
    protected array $data;

    /**
     * @param  array<int, array{date: string, nama_hari: string, total: int, online: int, kiosk: int, assisted: int}>  $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Mengembalikan baris data untuk sheet Per Hari.
     *
     * @return array<int, array<int, string|int>>
     */
    public function array(): array
    {
        return array_map(fn (array $item): array => [
            $item['date'],
            $item['nama_hari'],
            $item['total'],
            $item['online'],
            $item['kiosk'],
            $item['assisted'],
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
            'Tanggal',
            'Hari',
            'Total',
            'Online',
            'Kiosk',
            'Langsung',
        ];
    }

    /**
     * Judul sheet.
     */
    public function title(): string
    {
        return 'Per Hari';
    }
}
