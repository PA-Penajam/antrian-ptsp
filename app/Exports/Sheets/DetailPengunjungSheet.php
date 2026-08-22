<?php

namespace App\Exports\Sheets;

use App\Exports\Concerns\SanitizesCellValues;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DetailPengunjungSheet implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    use SanitizesCellValues;

    /**
     * Data detail pengunjung dari report builder.
     *
     * @var array<int, array{no: int, tanggal: string, nama: string, alamat: string, layanan: string}>
     */
    protected array $data;

    /**
     * @param  array<int, array{no: int, tanggal: string, nama: string, alamat: string, layanan: string}>  $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return array_map(fn (array $item): array => [
            $item['no'],
            $this->sanitizeCellValue($item['tanggal']),
            $this->sanitizeCellValue($item['nama']),
            $this->sanitizeCellValue($item['alamat']),
            $this->sanitizeCellValue($item['layanan']),
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
            'No',
            'Tanggal Pendaftaran',
            'Nama Pengunjung',
            'Alamat/Wilayah',
            'Layanan yang diambil',
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
        return 'Detail Pengunjung';
    }
}
