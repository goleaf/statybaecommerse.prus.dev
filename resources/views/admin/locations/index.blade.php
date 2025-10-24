<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Locations</title>
</head>
<body>
    <h1>Locations</h1>

    @if (session('status'))
        <div>{{ session('status') }}</div>
    @endif

    @if ($locations->isEmpty())
        <p>No locations found.</p>
    @else
        <table border="1" cellpadding="4" cellspacing="0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Country</th>
                    <th>Enabled</th>
                    <th>Default</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($locations as $location)
                    <tr>
                        <td>{{ $location->name }}</td>
                        <td>{{ $location->code }}</td>
                        <td>{{ $location->type ?? '—' }}</td>
                        <td>{{ optional($location->country)->name ?? $location->country_code ?? '—' }}</td>
                        <td>{{ $location->is_enabled ? 'Yes' : 'No' }}</td>
                        <td>{{ $location->is_default ? 'Yes' : 'No' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>

