<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Search events</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 860px; margin: 2rem auto; padding: 0 1rem; }
        li { margin-bottom: .6rem; }
        pre { background: #f4f4f4; padding: .6rem; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Search events</h1>
    <form method="GET" action="/events/search">
        <input type="text" name="q" value="{{ $q }}" placeholder="try: lantern">
        <button type="submit">Search</button>
    </form>

    @if ($sql)
        <h3>SQL</h3>
        <pre>{{ $sql }}</pre>
        <pre>bindings: {{ json_encode($bindings) }}</pre>
        <p>{{ $events->count() }} result(s) shown (max 50)</p>
    @endif

    <ul>
        @foreach ($events as $event)
            <li><strong>{{ $event->title }}</strong> at {{ $event->venue->name }}<br>{{ $event->description }}</li>
        @endforeach
    </ul>
</body>
</html>
