<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cek Status Antrian</title>
</head>
<body>
    <h1>Cek Status Antrian</h1>

    <form method="GET" action="{{ url('/antrian/cek') }}">
        <label>
            Nomor Antrian
            <input type="text" name="ticket_number" value="{{ request('ticket_number') }}">
        </label>
        <label>
            Tanggal Layanan
            <input type="date" name="service_date" value="{{ request('service_date') }}">
        </label>
        <button type="submit">Cari</button>
    </form>

    @if (request()->filled('ticket_number') && request()->filled('service_date'))
        <section>
            <h2>Hasil Pencarian</h2>
            @if ($ticket)
                <p>Nomor: {{ $ticket->ticket_number }}</p>
                <p>Status: {{ $ticket->status }}</p>
            @else
                <p>Tiket tidak ditemukan.</p>
            @endif
        </section>
    @endif
</body>
</html>
