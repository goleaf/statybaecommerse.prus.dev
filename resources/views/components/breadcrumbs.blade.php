@props(['items' => []])

@php
    $breadcrumbs = collect([['label' => __('Home'), 'url' => url('/' . app()->getLocale())]])
        ->merge(collect($items))
        ->mapWithKeys(function ($item) {
            return [$item['url'] ?? '' => $item['label']];
        })
        ->toArray();
@endphp

<nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8" aria-label="Breadcrumb">
    <div class="py-4">
        <x-filament::breadcrumbs :breadcrumbs="$breadcrumbs" />
    </div>
</nav>
@push('scripts')
    @php
        $ldItems = [];
        $pos = 1;
        $trail = array_merge([["label" => __('Home'), "url" => url('/' . app()->getLocale())]], $items ?? []);
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
