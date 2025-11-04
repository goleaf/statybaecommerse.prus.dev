@props(['nodes' => collect(), 'level' => 0])

@if ($nodes->isNotEmpty())
    <ul class="space-y-2">
        @foreach ($nodes as $node)
            <li>
                @php
                    $hasChildren = ($node['children'] ?? collect())->isNotEmpty();
                    $isRoot = (int) $level === 0;
                    $indentClasses = $isRoot ? '' : 'border-l border-sage/30 pl-3';
                    $linkColor = $isRoot ? 'text-sage hover:text-sage/80' : 'text-sage/90 hover:text-sage';
                @endphp
                <div @if($hasChildren) x-data="{ open: false }" @endif class="flex flex-col {{ $indentClasses }}">
                    <div class="flex items-start gap-2">
                        @if ($hasChildren)
                            <button type="button"
                                    @click="open = !open"
                                    class="mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded hover:bg-sage/10 focus:outline-none focus:ring-2 focus:ring-sage/40"
                                    aria-label="Toggle">
                                <svg class="h-4 w-4 text-sage transition-transform duration-200" :class="open ? 'rotate-90' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        @else
                            <span class="mt-0.5 inline-block h-5 w-5"></span>
                        @endif

                        <a href="{{ route('localized.categories.show', ['locale' => app()->getLocale(), 'category' => $node['slug']]) }}"
                           class="text-sm {{ $linkColor }} transition-colors">
                            {{ $node['name'] }}
                        </a>
                    </div>

                    @if ($hasChildren)
                        <div class="ml-6 mt-1" x-show="open" x-cloak>
                            <x-category.tree :nodes="$node['children']" :level="$level + 1" />
                        </div>
                    @endif
                </div>
            </li>
        @endforeach
    </ul>
@endif
