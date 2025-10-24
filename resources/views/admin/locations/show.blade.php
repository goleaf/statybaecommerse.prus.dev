<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Location Details</title>
</head>
<body>
    <h1>{{ $location->name }}</h1>

    <dl>
        <dt>Code</dt>
        <dd>{{ $location->code }}</dd>
        <dt>Type</dt>
        <dd>{{ $location->type ?? '—' }}</dd>
        <dt>Country</dt>
        <dd>{{ optional($location->country)->name ?? $location->country_code ?? '—' }}</dd>
        <dt>City</dt>
        <dd>{{ $location->city ?? '—' }}</dd>
        <dt>Full Address</dt>
        <dd>{{ $location->full_address ?: '—' }}</dd>
    </dl>
</body>
</html>

