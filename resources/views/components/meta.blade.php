@props([
    'title' => null,
    'description' => null,
    'ogTitle' => null,
    'ogDescription' => null,
    'ogImage' => null,
    'ogType' => 'website',
    'twitterCard' => 'summary_large_image',
    'twitterTitle' => null,
    'twitterDescription' => null,
    'robots' => null,
    'prev' => null,
    'next' => null,
    'canonical' => null,
    'preloadImage' => null,
    'preloadSrcset' => null,
    'preloadSizes' => null,
    'jsonld' => null,
    'alternateLocales' => null,
])

@php
    $metaTitle = $title;
    $openGraphTitle = $ogTitle ?? ($metaTitle ?? config('app.name'));
    $metaDescription = $description;
    $openGraphDescription = $ogDescription ?? $metaDescription;
    $twTitle = $twitterTitle ?? $openGraphTitle;
    $twDescription = $twitterDescription ?? $openGraphDescription;
    $defaultImage = asset('og-image.jpg');
    $effectiveOgImage = $ogImage ?: $defaultImage;
@endphp

@if ($metaDescription)
    <meta name="description" content="{{ $metaDescription }}" />
@endif

@if ($robots)
    <meta name="robots" content="{{ $robots }}" />
@endif

@if ($openGraphTitle)
    <meta property="og:title" content="{{ $openGraphTitle }}" />
@endif
@if ($openGraphDescription)
    <meta property="og:description" content="{{ $openGraphDescription }}" />
@endif
@if ($ogType)
    <meta property="og:type" content="{{ $ogType }}" />
@endif
@if ($effectiveOgImage)
    <meta property="og:image" content="{{ $effectiveOgImage }}" />
@endif

@if ($twitterCard)
    <meta name="twitter:card" content="{{ $twitterCard }}" />
@endif
@if ($twTitle)
    <meta name="twitter:title" content="{{ $twTitle }}" />
@endif
@if ($twDescription)
    <meta name="twitter:description" content="{{ $twDescription }}" />
@endif
@if ($effectiveOgImage)
    <meta name="twitter:image" content="{{ $effectiveOgImage }}" />
@endif

@if ($prev)
    <link rel="prev" href="{{ $prev }}" />
@endif
@if ($next)
    <link rel="next" href="{{ $next }}" />
@endif
@if ($canonical)
    <link rel="canonical" href="{{ $canonical }}" />
@endif

@if ($preloadImage || $preloadSrcset)
    <link rel="preload" as="image"
          @if ($preloadImage) href="{{ $preloadImage }}" @endif
          @if ($preloadSrcset) imagesrcset="{{ $preloadSrcset }}" @endif
          @if ($preloadSizes) imagesizes="{{ $preloadSizes }}" @endif />
@endif

@if (is_array($alternateLocales) && !empty($alternateLocales))
    @foreach ($alternateLocales as $locale => $href)
        <link rel="alternate" hreflang="{{ $locale }}" href="{{ $href }}" />
    @endforeach
@endif

@if (!empty($jsonld))
    <script type="application/ld+json">{!! $jsonld !!}</script>
@endif
