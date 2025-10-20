@props([
    'title' => null,
    'description' => null,
    'ogTitle' => null,
    'ogDescription' => null,
    'ogImage' => null,
    'ogType' => 'website',
    'ogUrl' => null,
    'twitterCard' => 'summary_large_image',
    'twitterTitle' => null,
    'twitterDescription' => null,
    'twitterUrl' => null,
    'robots' => null,
    'prev' => null,
    'next' => null,
    'canonical' => null,
    'keywords' => null,
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
    $jsonLdBlocks = collect(
        is_array($jsonld)
            ? (array_is_list($jsonld) ? $jsonld : [$jsonld])
            : ($jsonld ? [$jsonld] : [])
    )
        ->map(function ($block) {
            if (is_array($block)) {
                return json_encode($block, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }

            return (string) $block;
        })
        ->filter(static fn ($block) => $block !== '')
        ->values();
@endphp

@if ($title)
    <meta property="og:site_name" content="{{ config('app.name') }}" />
@endif

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
@if ($ogUrl ?? $canonical)
    <meta property="og:url" content="{{ $ogUrl ?? $canonical }}" />
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
@if ($twitterUrl ?? $canonical)
    <meta name="twitter:url" content="{{ $twitterUrl ?? $canonical }}" />
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

@if ($keywords)
    <meta name="keywords" content="{{ is_array($keywords) ? implode(', ', $keywords) : $keywords }}" />
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

@if ($jsonLdBlocks->isNotEmpty())
    @foreach ($jsonLdBlocks as $block)
        <script type="application/ld+json">{!! $block !!}</script>
    @endforeach
@endif
