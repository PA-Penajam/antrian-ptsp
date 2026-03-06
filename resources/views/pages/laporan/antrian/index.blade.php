<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Antrian</title>
</head>
<body>
    <h1>Laporan Antrian</h1>
    <p>Periode: {{ $from }} s.d. {{ $to }}</p>

    <section>
        <h2>By Service</h2>
        @foreach ($report['by_service'] as $name => $count)
            <p>{{ $name }}: {{ $count }}</p>
        @endforeach
    </section>

    <section>
        <h2>By Counter</h2>
        @foreach ($report['by_counter'] as $name => $count)
            <p>{{ $name }}: {{ $count }}</p>
        @endforeach
    </section>

    <section>
        <h2>By Officer</h2>
        @foreach ($report['by_officer'] as $name => $count)
            <p>{{ $name }}: {{ $count }}</p>
        @endforeach
    </section>

    <section>
        <h2>By Status</h2>
        @foreach ($report['by_status'] as $status => $count)
            <p>{{ $status }}: {{ $count }}</p>
        @endforeach
    </section>
</body>
</html>
