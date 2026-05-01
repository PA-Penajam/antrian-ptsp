<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Bulanan Pendaftar Layanan</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            margin: 40px;
            color: #1a1a1a;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1a1a1a;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0 0 4px 0;
            font-size: 16px;
            font-weight: bold;
        }
        .header p {
            margin: 2px 0;
            font-size: 11px;
            color: #333;
        }
        .title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin: 24px 0;
            line-height: 1.6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            page-break-inside: auto;
        }
        th, td {
            border: 1px solid #333;
            padding: 5px 8px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-size: 10px;
            font-weight: bold;
        }
        td {
            font-size: 10px;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin: 24px 0 8px 0;
            padding-bottom: 4px;
            border-bottom: 1px solid #ccc;
        }
        .footer {
            margin-top: 50px;
            text-align: right;
            page-break-inside: avoid;
        }
        .footer-date {
            font-size: 10px;
            margin-bottom: 10px;
            color: #555;
        }
        .signature-area {
            margin-top: 60px;
            text-align: center;
        }
        .signature-area p {
            margin: 2px 0;
            font-size: 11px;
        }
        .signature-line {
            margin-top: 50px;
            margin-bottom: 4px;
        }
        .no-data {
            text-align: center;
            font-style: italic;
            color: #888;
            padding: 12px;
        }
    </style>
</head>
<body>
    {{-- Kop Surat --}}
    <div class="header">
        <h2>{{ config('institution.name') }}</h2>
        @if (config('institution.address'))
            <p>{{ config('institution.address') }}</p>
        @endif
        <p>Jam Operasional: {{ config('institution.operating_hours') }}</p>
    </div>

    {{-- Judul Laporan --}}
    <div class="title">
        LAPORAN BULANAN PENDAFTAR LAYANAN<br>
        {{ $judulBulan }}
    </div>

    {{-- A. Ringkasan --}}
    <div class="section-title">A. Ringkasan Statistik</div>
    <table>
        <tr>
            <th style="width:75%">Keterangan</th>
            <th style="width:25%" class="text-center">Jumlah</th>
        </tr>
        <tr>
            <td>Total Pendaftar</td>
            <td class="text-center">{{ number_format($ringkasan['total'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Selesai Dilayani</td>
            <td class="text-center">{{ number_format($ringkasan['completed'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Menunggu / Dalam Proses</td>
            <td class="text-center">{{ number_format($ringkasan['waiting'], 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Dibatalkan / Dilewati</td>
            <td class="text-center">{{ number_format($ringkasan['cancelled'], 0, ',', '.') }}</td>
        </tr>
    </table>

    {{-- B. Per Layanan --}}
    <div class="section-title">B. Rekap Per Layanan</div>
    @if (count($perLayanan) > 0)
        <table>
            <tr>
                <th>Layanan</th>
                <th class="text-center">Total</th>
                <th class="text-center">Selesai</th>
                <th class="text-center">Dibatalkan</th>
            </tr>
            @foreach ($perLayanan as $row)
                <tr>
                    <td>{{ $row['name'] }}</td>
                    <td class="text-center">{{ $row['total'] }}</td>
                    <td class="text-center">{{ $row['completed'] }}</td>
                    <td class="text-center">{{ $row['cancelled'] }}</td>
                </tr>
            @endforeach
        </table>
    @else
        <p class="no-data">Tidak ada data layanan pada periode ini.</p>
    @endif

    {{-- C. Per Hari --}}
    <div class="section-title">C. Detail Per Hari</div>
    @if (\Illuminate\Support\Arr::first($perHari, fn ($r) => $r['total'] > 0))
        <table>
            <tr>
                <th>Tanggal</th>
                <th>Hari</th>
                <th class="text-center">Total</th>
                <th class="text-center">Online</th>
                <th class="text-center">Kiosk</th>
                <th class="text-center">Petugas</th>
            </tr>
            @foreach ($perHari as $row)
                @if ($row['total'] > 0)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($row['date'])->locale('id')->isoFormat('D MMM') }}</td>
                        <td>{{ $row['nama_hari'] }}</td>
                        <td class="text-center">{{ $row['total'] }}</td>
                        <td class="text-center">{{ $row['online'] }}</td>
                        <td class="text-center">{{ $row['kiosk'] }}</td>
                        <td class="text-center">{{ $row['assisted'] }}</td>
                    </tr>
                @endif
            @endforeach
        </table>
    @else
        <p class="no-data">Tidak ada data harian pada periode ini.</p>
    @endif

    {{-- D. Per Channel --}}
    <div class="section-title">D. Distribusi Channel</div>
    @if (\Illuminate\Support\Arr::first($perChannel, fn ($r) => $r['total'] > 0))
        <table>
            <tr>
                <th>Channel</th>
                <th class="text-center">Jumlah</th>
                <th class="text-center">Persentase</th>
            </tr>
            @foreach ($perChannel as $row)
                <tr>
                    <td>{{ $row['channel'] }}</td>
                    <td class="text-center">{{ $row['total'] }}</td>
                    <td class="text-center">{{ number_format($row['persen'], 1, ',', '.') }}%</td>
                </tr>
            @endforeach
        </table>
    @else
        <p class="no-data">Tidak ada data channel pada periode ini.</p>
    @endif

    {{-- Footer Tanda Tangan --}}
    <div class="footer">
        <p class="footer-date">
            {{ config('institution.name') }}, {{ now()->locale('id')->translatedFormat('d F Y') }}
        </p>
        <div class="signature-area">
            <p>Kepala Sub Bagian PTSP</p>
            <p class="signature-line">(_____________________________)</p>
            <p>NIP. ________________________</p>
        </div>
    </div>
</body>
</html>
