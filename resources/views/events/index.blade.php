<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Events</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 860px; margin: 2rem auto; padding: 0 1rem; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: .35rem .6rem; text-align: left; }
        pre { background: #f4f4f4; padding: .6rem; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Events <small>({{ $mode }} mode)</small></h1>
    <p>Queries sent for this page: <strong>{{ $queryCount }}</strong></p>

    @if ($filter)
        <h3>SQL for the "{{ $filter }}" filter</h3>
        <pre>{{ $sql }}</pre>
        <pre>bindings: {{ json_encode($bindings) }}</pre>
    @endif

    <details>
        <summary>First queries in the log</summary>
        @foreach ($queries as $query)
            <pre>{{ $query }}</pre>
        @endforeach
    </details>

    <table>
        <tr><th>Title</th><th>Venue</th><th>Participants</th></tr>
        @foreach ($rows as $row)
            <tr>
                <td>{{ $row['title'] }}</td>
                <td>{{ $row['venue'] }}</td>
                <td>{{ $row['participants'] }}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>
