<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Frontdesk Antrian</title>
</head>
<body>
    <h1>Frontdesk Antrian</h1>

    @if ($ticket)
        <section>
            <h2>Tiket Berhasil Dibuat</h2>
            <p>Nomor: {{ $ticket->ticket_number }}</p>
            <p>Status: {{ $ticket->status }}</p>
        </section>
    @endif

    @if ($checkedInTicket)
        <section>
            <h2>Check-in Berhasil</h2>
            <p>Nomor: {{ $checkedInTicket->ticket_number }}</p>
            <p>Status: {{ $checkedInTicket->status }}</p>
        </section>
    @endif
</body>
</html>
