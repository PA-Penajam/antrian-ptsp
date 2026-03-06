<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Display Antrian PTSP</title>
</head>
<body>
    <h1>Display Antrian PTSP</h1>

    <section>
        <h2>Sedang Dipanggil</h2>
        @forelse ($currentCalls as $ticket)
            <article>
                <p>{{ $ticket->ticket_number }}</p>
                <p>{{ $ticket->counter?->name ?? 'Loket belum ditetapkan' }}</p>
            </article>
        @empty
            <p>Tidak ada panggilan aktif.</p>
        @endforelse
    </section>

    <section>
        <h2>Riwayat Panggilan</h2>
        @forelse ($recentCalls as $ticket)
            <article>
                <p>{{ $ticket->ticket_number }}</p>
                <p>{{ $ticket->counter?->name ?? 'Loket belum ditetapkan' }}</p>
            </article>
        @empty
            <p>Belum ada riwayat panggilan.</p>
        @endforelse
    </section>
</body>
</html>
