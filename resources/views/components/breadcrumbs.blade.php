@props(['items' => []])
<nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8" aria-label="Breadcrumb">
    <ol role="list" class="flex items-center space-x-2 py-4 text-sm text-gray-600 dark:text-gray-300">
        <li>
            <a href="{{ url('/' . app()->getLocale()) }}" class="hover:underline">{{ __('Home') }}</a>
        </li>
        @foreach ($items as $item)
            <li>
                <span class="mx-2">/</span>
                @if (!empty($item['url']))
                    <a href="{{ $item['url'] }}" class="hover:underline">{{ $item['label'] }}</a>
                @else
                    <span aria-current="page" class="font-medium">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
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
