@php
    $title = $title ?? (View::shared('title') ?? config('app.name'));
    $desc = trim($description ?? (View::shared('description') ?? ''));
    $url = url()->current();
    $image = $image ?? asset('og-image.jpg');
@endphp
<meta property="og:type" content="website" />
<meta property="og:site_name" content="{{ config('app.name') }}" />
<meta property="og:title" content="{{ $title }}" />
@if ($desc !== '')
    <meta property="og:description" content="{{ $desc }}" />
@endif
<meta property="og:url" content="{{ $url }}" />
<meta property="og:image" content="{{ $image }}" />
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="{{ $title }}" />
@if ($desc !== '')
    <meta name="twitter:description" content="{{ $desc }}" />
@endif
<meta name="twitter:image" content="{{ $image }}" />
