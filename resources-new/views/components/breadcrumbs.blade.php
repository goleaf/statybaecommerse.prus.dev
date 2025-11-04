@props(['items' => []])

@php
    $breadcrumbs = collect([['label' => __('home.homepage'), 'url' => url('/' . app()->getLocale())]])
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
        $trail = array_merge([["label" => __('home.homepage'), "url" => url('/' . app()->getLocale())]], $items ?? []);
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
