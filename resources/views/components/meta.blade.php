@props([
    'title' => null,
    'description' => null,
    'ogTitle' => null,
    'ogDescription' => null,
    'ogImage' => null,
    'ogImageAlt' => null,
    'ogImageWidth' => null,
    'ogImageHeight' => null,
    'ogImageType' => null,
    'ogType' => 'website',
    'ogUrl' => null,
    'ogLocale' => null,
    'twitterCard' => 'summary_large_image',
    'twitterTitle' => null,
    'twitterDescription' => null,
    'twitterUrl' => null,
    'twitterSite' => null,
    'twitterCreator' => null,
    'twitterImageAlt' => null,
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
    // Determine the title that will be used across Open Graph and Twitter previews.
    $openGraphTitle = $ogTitle ?? ($metaTitle ?? config('app.name'));
    $metaDescription = $description;
    $openGraphDescription = $ogDescription ?? $metaDescription;
    $twTitle = $twitterTitle ?? $openGraphTitle;
    $twDescription = $twitterDescription ?? $openGraphDescription;
    $defaultImage = og_placeholder_url();
    $effectiveOgImage = $ogImage ?: $defaultImage;
    // Ensure we always expose a shareable URL even when a canonical link is not provided explicitly.
    $shareUrl = $ogUrl
        ?? $canonical
        ?? (function () {
            try {
                return url()->current();
            } catch (\Throwable $throwable) {
                return null;
            }
        })();
    // Normalize locale for Open Graph tags and provide fallbacks if nothing was passed in.
    $primaryLocale = $ogLocale
        ?? (function () {
            $locale = app()->getLocale();
            return is_string($locale) ? str_replace('_', '-', $locale) : null;
        })();
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
@if ($shareUrl)
    <meta property="og:url" content="{{ $shareUrl }}" />
@endif
@if ($effectiveOgImage)
    <meta property="og:image" content="{{ $effectiveOgImage }}" />
    {{-- Provide richer previews by exposing secure URLs and asset metadata when available. --}}
    @php
        $secureImageUrl = null;
        if (is_string($effectiveOgImage) && \Illuminate\Support\Str::startsWith($effectiveOgImage, 'http://')) {
            $secureImageUrl = preg_replace('/^http:/i', 'https:', $effectiveOgImage);
        } elseif (is_string($effectiveOgImage) && \Illuminate\Support\Str::startsWith($effectiveOgImage, 'https://')) {
            $secureImageUrl = $effectiveOgImage;
        }
    @endphp
    @if ($secureImageUrl)
        <meta property="og:image:secure_url" content="{{ $secureImageUrl }}" />
    @endif
    @if ($ogImageAlt ?? $openGraphTitle)
        <meta property="og:image:alt" content="{{ $ogImageAlt ?? $openGraphTitle }}" />
    @endif
    @if ($ogImageWidth)
        <meta property="og:image:width" content="{{ $ogImageWidth }}" />
    @endif
    @if ($ogImageHeight)
        <meta property="og:image:height" content="{{ $ogImageHeight }}" />
    @endif
    @if ($ogImageType)
        <meta property="og:image:type" content="{{ $ogImageType }}" />
    @endif
@endif
@if ($primaryLocale)
    <meta property="og:locale" content="{{ $primaryLocale }}" />
@endif
@if (is_iterable($alternateLocales))
    @foreach ($alternateLocales as $localeCode => $href)
        @php
            $normalizedAlternate = is_string($localeCode) ? str_replace('_', '-', $localeCode) : null;
        @endphp
        @if ($normalizedAlternate && $normalizedAlternate !== $primaryLocale)
            <meta property="og:locale:alternate" content="{{ $normalizedAlternate }}" />
        @endif
    @endforeach
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
@if ($twitterUrl ?? $shareUrl)
    <meta name="twitter:url" content="{{ $twitterUrl ?? $shareUrl }}" />
@endif
@if ($twitterSite)
    <meta name="twitter:site" content="{{ $twitterSite }}" />
@endif
@if ($twitterCreator)
    <meta name="twitter:creator" content="{{ $twitterCreator }}" />
@endif
@if ($twitterImageAlt ?? $ogImageAlt ?? $openGraphTitle)
    <meta name="twitter:image:alt" content="{{ $twitterImageAlt ?? $ogImageAlt ?? $openGraphTitle }}" />
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
