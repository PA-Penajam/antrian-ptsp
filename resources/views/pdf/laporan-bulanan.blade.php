<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Bulanan Pendaftar Layanan - {{ $judulBulan }}</title>
    <style>
        @page {
            margin: 35px 40px 45px 40px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }
        /* Kop Surat Instansi */
        .kop-surat {
            text-align: center;
            border-bottom: 3px double #0f172a;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .kop-instansi {
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #0f172a;
            margin: 0 0 3px 0;
        }
        .kop-sub {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #334155;
            margin: 0 0 4px 0;
        }
        .kop-meta {
            font-size: 9px;
            color: #64748b;
            margin: 0;
        }

        /* Judul Laporan */
        .report-title-box {
            text-align: center;
            margin: 16px 0 20px 0;
        }
        .report-title {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
            margin: 0;
        }
        .report-period {
            font-size: 11px;
            font-weight: bold;
            color: #0284c7;
            margin-top: 3px;
        }

        /* Section Headings */
        .section-header {
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin: 18px 0 8px 0;
            padding-bottom: 4px;
            border-bottom: 1.5px solid #cbd5e1;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            page-break-inside: auto;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 5.5px 8px;
            vertical-align: middle;
        }
        th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        td {
            font-size: 9.5px;
        }
        tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        tfoot tr {
            background-color: #f1f5f9;
            font-weight: bold;
        }
        tfoot td {
            border-top: 2px solid #0f172a;
            font-weight: bold;
        }

        /* Alignment Utilities */
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }
        .text-emerald { color: #047857; font-weight: bold; }
        .text-red { color: #b91c1c; font-weight: bold; }
        .text-sky { color: #0369a1; font-weight: bold; }

        /* Empty State */
        .no-data {
            text-align: center;
            font-style: italic;
            color: #94a3b8;
            padding: 12px;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 4px;
        }

        /* Signature Section */
        .footer-signatures {
            margin-top: 36px;
            page-break-inside: avoid;
            width: 100%;
        }
        .signature-table {
            width: 100%;
            border: none;
            margin: 0;
        }
        .signature-table td {
            border: none;
            padding: 0;
            vertical-align: top;
            background: transparent !important;
        }
        .signature-box {
            text-align: center;
            width: 220px;
            float: right;
        }
        .signature-date {
            font-size: 9.5px;
            color: #475569;
            margin-bottom: 6px;
        }
        .signature-title {
            font-size: 10px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 55px;
        }
        .signature-name {
            font-size: 10px;
            font-weight: bold;
            text-decoration: underline;
            color: #0f172a;
            margin: 0;
        }
        .signature-nip {
            font-size: 9px;
            color: #64748b;
            margin: 2px 0 0 0;
        }

        /* Print Timestamp Footer */
        .print-meta {
            margin-top: 25px;
            padding-top: 6px;
            border-top: 1px dashed #e2e8f0;
            font-size: 8px;
            color: #94a3b8;
            text-align: left;
        }
    </style>
</head>
<body>
    {{-- Kop Surat Resmi --}}
    <div class="kop-surat">
        <h1 class="kop-instansi">{{ config('institution.name', 'Pengadilan Agama') }}</h1>
        <div class="kop-sub">Pelayanan Terpadu Satu Pintu (PTSP)</div>
        <p class="kop-meta">
            @if (config('institution.address'))
                {{ config('institution.address') }} &bull;
            @endif
            Jam Operasional: {{ config('institution.operating_hours', '08:00 - 15:00 WITA') }}
        </p>
    </div>

    {{-- Judul Dokumen --}}
    <div class="report-title-box">
        <h2 class="report-title">LAPORAN BULANAN REKAPITULASI PENDAFTAR LAYANAN</h2>
        <div class="report-period">Periode: {{ $judulBulan }}</div>
    </div>

    {{-- A. Ringkasan Statistik --}}
    <div class="section-header">A. Ringkasan Eksekutif Pelayanan</div>
    @php
        $tot = $ringkasan['total'];
        $comp = $ringkasan['completed'];
        $wait = $ringkasan['waiting'];
        $canc = $ringkasan['cancelled'];
        $compRate = $tot > 0 ? round(($comp / $tot) * 100, 1) : 0;
        $cancRate = $tot > 0 ? round(($canc / $tot) * 100, 1) : 0;
    @endphp
    <table>
        <thead>
            <tr>
                <th class="text-left" style="width: 50%;">Indikator Pelayanan</th>
                <th class="text-center" style="width: 25%;">Volume (Tiket)</th>
                <th class="text-center" style="width: 25%;">Rasio (%)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="font-bold">Total Pendaftar Tercatat</td>
                <td class="text-center font-mono font-bold">{{ number_format($tot, 0, ',', '.') }}</td>
                <td class="text-center font-mono font-bold">100.0%</td>
            </tr>
            <tr>
                <td>Selesai Dilayani Petugas</td>
                <td class="text-center font-mono text-emerald">{{ number_format($comp, 0, ',', '.') }}</td>
                <td class="text-center font-mono text-emerald">{{ number_format($compRate, 1, ',', '.') }}%</td>
            </tr>
            <tr>
                <td>Dalam Proses / Menunggu Antrian</td>
                <td class="text-center font-mono">{{ number_format($wait, 0, ',', '.') }}</td>
                <td class="text-center font-mono">{{ $tot > 0 ? number_format(round(($wait / $tot) * 100, 1), 1, ',', '.') : '0.0' }}%</td>
            </tr>
            <tr>
                <td>Dibatalkan / Dilewati</td>
                <td class="text-center font-mono text-red">{{ number_format($canc, 0, ',', '.') }}</td>
                <td class="text-center font-mono text-red">{{ number_format($cancRate, 1, ',', '.') }}%</td>
            </tr>
        </tbody>
    </table>

    {{-- B. Rekap Per Layanan --}}
    <div class="section-header">B. Rekapitulasi Berdasarkan Jenis Layanan</div>
    @if (count($perLayanan) > 0)
        @php
            $sumLayananTotal = collect($perLayanan)->sum('total');
            $sumLayananComp = collect($perLayanan)->sum('completed');
            $sumLayananCanc = collect($perLayanan)->sum('cancelled');
        @endphp
        <table>
            <thead>
                <tr>
                    <th class="text-center" style="width: 5%;">No</th>
                    <th class="text-left" style="width: 47%;">Jenis Layanan</th>
                    <th class="text-center" style="width: 16%;">Total Pendaftar</th>
                    <th class="text-center" style="width: 16%;">Selesai</th>
                    <th class="text-center" style="width: 16%;">Dibatalkan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($perLayanan as $index => $row)
                    <tr>
                        <td class="text-center font-mono">{{ $index + 1 }}</td>
                        <td class="font-bold">{{ $row['name'] }}</td>
                        <td class="text-center font-mono">{{ number_format($row['total'], 0, ',', '.') }}</td>
                        <td class="text-center font-mono text-emerald">{{ number_format($row['completed'], 0, ',', '.') }}</td>
                        <td class="text-center font-mono text-red">{{ number_format($row['cancelled'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="text-center">JUMLAH TOTAL</td>
                    <td class="text-center font-mono">{{ number_format($sumLayananTotal, 0, ',', '.') }}</td>
                    <td class="text-center font-mono text-emerald">{{ number_format($sumLayananComp, 0, ',', '.') }}</td>
                    <td class="text-center font-mono text-red">{{ number_format($sumLayananCanc, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    @else
        <p class="no-data">Tidak ada data transaksi layanan pada periode ini.</p>
    @endif

    {{-- C. Distribusi Kanal --}}
    <div class="section-header">C. Distribusi Kanal Registrasi Antrian</div>
    @if (\Illuminate\Support\Arr::first($perChannel, fn ($r) => $r['total'] > 0))
        @php
            $sumChannelTotal = collect($perChannel)->sum('total');
        @endphp
        <table>
            <thead>
                <tr>
                    <th class="text-center" style="width: 5%;">No</th>
                    <th class="text-left" style="width: 55%;">Kanal Pendaftaran</th>
                    <th class="text-center" style="width: 20%;">Jumlah Tiket</th>
                    <th class="text-center" style="width: 20%;">Persentase (%)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($perChannel as $index => $row)
                    <tr>
                        <td class="text-center font-mono">{{ $index + 1 }}</td>
                        <td class="font-bold">{{ $row['channel'] }}</td>
                        <td class="text-center font-mono">{{ number_format($row['total'], 0, ',', '.') }}</td>
                        <td class="text-center font-mono">{{ number_format($row['persen'], 1, ',', '.') }}%</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="text-center">TOTAL KANAL</td>
                    <td class="text-center font-mono">{{ number_format($sumChannelTotal, 0, ',', '.') }}</td>
                    <td class="text-center font-mono">100.0%</td>
                </tr>
            </tfoot>
        </table>
    @else
        <p class="no-data">Tidak ada data kanal registrasi pada periode ini.</p>
    @endif

    {{-- D. Detail Per Hari --}}
    <div class="section-header">D. Rekapitulasi Beban Antrian Harian</div>
    @if (\Illuminate\Support\Arr::first($perHari, fn ($r) => $r['total'] > 0))
        <table>
            <thead>
                <tr>
                    <th class="text-center" style="width: 20%;">Tanggal</th>
                    <th class="text-center" style="width: 16%;">Hari</th>
                    <th class="text-center" style="width: 16%;">Total Tiket</th>
                    <th class="text-center" style="width: 16%;">Online Booking</th>
                    <th class="text-center" style="width: 16%;">Kiosk Mandiri</th>
                    <th class="text-center" style="width: 16%;">Dibantu Petugas</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($perHari as $row)
                    @if ($row['total'] > 0)
                        <tr>
                            <td class="text-center font-mono">{{ \Carbon\Carbon::parse($row['date'])->translatedFormat('d/m/Y') }}</td>
                            <td class="text-center">{{ $row['nama_hari'] }}</td>
                            <td class="text-center font-mono font-bold">{{ number_format($row['total'], 0, ',', '.') }}</td>
                            <td class="text-center font-mono">{{ number_format($row['online'], 0, ',', '.') }}</td>
                            <td class="text-center font-mono">{{ number_format($row['kiosk'], 0, ',', '.') }}</td>
                            <td class="text-center font-mono">{{ number_format($row['assisted'], 0, ',', '.') }}</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    @else
        <p class="no-data">Tidak ada catatan aktivitas harian pada periode ini.</p>
    @endif

    {{-- E. Pengesahan / Tanda Tangan --}}
    <div class="footer-signatures">
        <table class="signature-table">
            <tr>
                <td style="width: 50%;"></td>
                <td style="width: 50%;">
                    <div class="signature-box">
                        <div class="signature-date">
                            {{ config('institution.city', 'Penajam') }}, {{ now()->translatedFormat('d F Y') }}
                        </div>
                        <div class="signature-title">
                            Penanggung Jawab Pelayanan PTSP
                        </div>
                        <p class="signature-name">( __________________________ )</p>
                        <p class="signature-nip">NIP. ...................................................</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Meta Cetak --}}
    <div class="print-meta">
        Dokumen ini dihasilkan secara otomatis oleh Sistem Antrian PTSP {{ config('institution.name') }} pada {{ now()->translatedFormat('d F Y, H:i:s') }} WITA.
    </div>
</body>
</html>

