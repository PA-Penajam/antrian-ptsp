<?php

namespace App\Exports\Sheets;

use App\Exports\Concerns\SanitizesCellValues;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DetailPengunjungSheet implements FromArray, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithStyles, WithTitle
{
    use SanitizesCellValues;

    /**
     * Data detail pengunjung dari report builder.
     *
     * @var array<int, array{no: int, tanggal: string, telepon: string, nama: string, alamat: string, layanan: string}>
     */
    protected array $data;

    /**
     * @param  array<int, array{no: int, tanggal: string, telepon: string, nama: string, alamat: string, layanan: string}>  $data
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
            $this->sanitizeCellValue($item['telepon']),
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
            'No. HP/Telepon',
            'Nama Pengunjung',
            'Alamat/Wilayah',
            'Layanan yang diambil',
        ];
    }

    /**
     * Format kolom No. HP sebagai teks agar angka 0 di depan
     * (mis. 0812...) dan awalan + (mis. +628...) tidak diubah Excel.
     *
     * @return array<string, string>
     */
    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT,
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
