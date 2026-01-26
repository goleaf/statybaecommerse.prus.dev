<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ __('admin.locations_page.title') }}</title>
</head>
<body>
    <h1>{{ __('admin.locations_page.title') }}</h1>

    @if (session('status'))
        <div>{{ session('status') }}</div>
    @endif

    @if ($locations->isEmpty())
        <p>{{ __('admin.locations_page.empty') }}</p>
    @else
        <table border="1" cellpadding="4" cellspacing="0">
            <thead>
                <tr>
                    <th>{{ __('admin.locations_page.columns.name') }}</th>
                    <th>{{ __('admin.locations_page.columns.code') }}</th>
                    <th>{{ __('admin.locations_page.columns.type') }}</th>
                    <th>{{ __('admin.locations_page.columns.country') }}</th>
                    <th>{{ __('admin.locations_page.columns.enabled') }}</th>
                    <th>{{ __('admin.locations_page.columns.default') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($locations as $location)
                    <tr>
                        <td>{{ $location->name }}</td>
                        <td>{{ $location->code }}</td>
                        <td>{{ $location->type ?? __('admin.common.not_available') }}</td>
                        <td>{{ optional($location->country)->name ?? $location->country_code ?? __('admin.common.not_available') }}</td>
                        <td>{{ $location->is_enabled ? __('messages.admin) : __('messages.admin) }}</td>
                        <td>{{ $location->is_default ? __('messages.admin) : __('messages.admin) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
