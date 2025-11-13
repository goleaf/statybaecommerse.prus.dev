@props(['items' => []])

@php
    // Ensure locale is set before using translations
    $request = request();
    $supportedConfig = config('app.supported_locales', 'lt,en');
    $supportedLocales = [];
    if (is_array($supportedConfig)) {
        $supportedLocales = array_filter($supportedConfig, fn ($locale): bool => is_string($locale) && $locale !== '');
    } elseif (is_string($supportedConfig)) {
        $supportedLocales = array_filter(
            array_map(fn (string $locale): string => trim($locale), explode(',', $supportedConfig)),
            fn (string $locale): bool => $locale !== ''
        );
    }
    $supportedLocales = array_values(array_map(fn (string $locale): string => trim($locale), $supportedLocales));
    
    $routeLocale = $request->route('locale');
    $queryLocale = $request->query('locale');
    $defaultLocale = config('app.locale', 'lt');
    
    $candidateLocales = array_values(array_filter([
        $routeLocale,
        $queryLocale,
        session('locale'),
        session('app.locale'),
        $request->cookie('app_locale'),
        auth()->check() ? (auth()->user()->preferred_locale ?? null) : null,
    ], fn ($candidate): bool => is_string($candidate) && $candidate !== ''));
    
    $locale = $defaultLocale;
    foreach ($candidateLocales as $candidate) {
        if (in_array($candidate, $supportedLocales, true)) {
            $locale = $candidate;
            break;
        }
    }
    
    if (!in_array($locale, $supportedLocales, true)) {
        $fallbackLocale = config('app.fallback_locale', $defaultLocale);
        $locale = in_array($fallbackLocale, $supportedLocales, true) ? $fallbackLocale : ($supportedLocales[0] ?? $defaultLocale);
    }
    
    app()->setLocale($locale);
    
    $breadcrumbs = collect([['label' => __('frontend.navigation.home'), 'url' => url('/' . $locale)]])
        ->merge(collect($items))
        ->mapWithKeys(function ($item) {
            return [$item['url'] ?? '' => $item['label']];
        })
        ->toArray();
@endphp

<nav class="breadcrumb-nav" aria-label="Breadcrumb">
    <div class="breadcrumb-container">
        <ol class="breadcrumb-list">
            @foreach($breadcrumbs as $url => $label)
                <li class="breadcrumb-item">
                    @if($url && $url !== url()->current())
                        <a href="{{ $url }}" class="breadcrumb-link">
                            {{ $label }}
                        </a>
                    @else
                        <span class="breadcrumb-current">
                            {{ $label }}
                        </span>
                    @endif
                    
                    @if(!$loop->last)
                        <svg class="breadcrumb-separator" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    @endif
                </li>
            @endforeach
        </ol>
    </div>
</nav>
@push('scripts')
    @php
        $ldItems = [];
        $pos = 1;
        $trail = array_merge([["label" => __('frontend.navigation.home'), "url" => url('/' . $locale)]], $items ?? []);
        foreach ($trail as $it) {
            if (!empty($it['label'])) {
                $ldItems[] = [
                    '@type' => 'ListItem',
                    'position' => $pos++,
                    'name' => $it['label'],
                    'item' => !empty($it['url']) ? $it['url'] : url()->current(),
                ];
            }
        }
    @endphp
    @if (!empty($ldItems))
        <script type="application/ld+json">
        {!! json_encode(['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $ldItems], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
        </script>
    @endif
@endpush
