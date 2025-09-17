@props(['nodes' => collect()])

@if ($nodes->isNotEmpty())
    <ul class="space-y-2">
        @foreach ($nodes as $node)
            <li>
                <a href="{{ route('localized.categories.show', ['locale' => app()->getLocale(), 'category' => $node['slug']]) }}"
                   class="text-sm text-gray-700 hover:underline">
                    {{ $node['name'] }}
                </a>
                @if (($node['children'] ?? collect())->isNotEmpty())
                    <div class="ml-4 mt-1">
                        <x-category.tree :nodes="$node['children']" />
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
@endif
