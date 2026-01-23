<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ __('admin.locations_page.details_title') }}</title>
</head>
<body>
    <h1>{{ $location->name }}</h1>

    <dl>
        <dt>{{ __('admin.locations_page.fields.code') }}</dt>
        <dd>{{ $location->code }}</dd>
        <dt>{{ __('admin.locations_page.fields.type') }}</dt>
        <dd>{{ $location->type ?? __('admin.common.not_available') }}</dd>
        <dt>{{ __('admin.locations_page.fields.country') }}</dt>
        <dd>{{ optional($location->country)->name ?? $location->country_code ?? __('admin.common.not_available') }}</dd>
        <dt>{{ __('admin.locations_page.fields.city') }}</dt>
        <dd>{{ $location->city ?? __('admin.common.not_available') }}</dd>
        <dt>{{ __('admin.locations_page.fields.full_address') }}</dt>
        <dd>{{ $location->full_address ?: __('admin.common.not_available') }}</dd>
    </dl>
</body>
</html>
