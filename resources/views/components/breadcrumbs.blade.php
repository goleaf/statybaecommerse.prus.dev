<nav class="breadcrumb-nav" aria-label="{{ __('frontend.navigation.breadcrumbs') }}">
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
    @if (!empty($ldItems))
        <script type="application/ld+json">
        {!! json_encode(['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $ldItems], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
        </script>
    @endif
@endpush
