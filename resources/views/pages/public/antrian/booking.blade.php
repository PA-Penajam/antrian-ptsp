<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ambil Antrian PTSP</title>
</head>
<body>
    <h1>Ambil Antrian PTSP</h1>

    <ul>
        @foreach ($services as $service)
            <li>{{ $service->name }}</li>
        @endforeach
    </ul>

    <form method="POST" action="{{ url('/antrian') }}">
        @csrf
        <label>
            Layanan
            <select name="service_id" required>
                @foreach ($services as $service)
                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                @endforeach
            </select>
        </label>
        <label>
            Tanggal Layanan
            <input type="date" name="service_date" required>
        </label>
        <label>
            Nama
            <input type="text" name="visitor_name" required>
        </label>
        <label>
            Identitas
            <input type="text" name="visitor_identifier">
        </label>
        <label>
            Telepon
            <input type="text" name="visitor_phone">
        </label>
        <label>
            Catatan
            <textarea name="notes"></textarea>
        </label>
        <button type="submit">Kirim</button>
    </form>

    @if ($ticket)
        <section>
            <h2>Booking Berhasil</h2>
            <p>Nomor: {{ $ticket->ticket_number }}</p>
            <p>Status: {{ $ticket->status }}</p>
        </section>
    @endif
</body>
</html>
