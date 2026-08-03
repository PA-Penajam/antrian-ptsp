<?php

namespace App\Exports\Concerns;

trait SanitizesCellValues
{
    /**
     * Sanitasi nilai teks agar tidak dieksekusi sebagai formula Excel.
     *
     * Excel/LibreOffice akan mengeksekusi string yang diawali karakter formula.
     * Menambahkan tanda kutip tunggal di depan memaksa aplikasi spreadsheet
     * memperlakukan nilai sebagai teks.
     */
    private function sanitizeCellValue(string $value): string
    {
        $trimmed = ltrim($value);
        $first = $trimmed === '' ? '' : $trimmed[0];

        if (in_array($first, ['=', '+', '-', '@'], true)) {
            return "'".$value;
        }

        return $value;
    }
}
