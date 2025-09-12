@props(['category', 'level' => 0])

@php
    $indentClass = match ($level) {
        0 => '',
        1 => 'ml-4',
        2 => 'ml-8',
        3 => 'ml-12',
        default => 'ml-16',
    };

    $textSizeClass = match ($level) {
        0 => 'text-sm font-semibold',
        1 => 'text-sm font-medium',
        2 => 'text-sm',
        3 => 'text-xs',
        default => 'text-xs',
    };

    $bgClass = match ($level) {
        0 => 'bg-gray-50',
        1 => 'bg-white',
        2 => 'bg-gray-25',
        default => 'bg-white',
    };
@endphp

<div class="{{ $indentClass }}">
    <div
         class="flex items-center justify-between py-3 px-4 rounded-md {{ $bgClass }} hover:bg-gray-100 group border border-gray-100">
        <div class="flex items-center space-x-3 flex-1 min-w-0">
            @if ($category['has_children'])
                <button
                        type="button"
                        @click="toggleCategory({{ $category['id'] }})"
                        class="flex-shrink-0 w-5 h-5 flex items-center justify-center text-gray-500 hover:text-gray-700 transition-colors rounded-full hover:bg-gray-200">
                    <svg
                         class="w-3 h-3 transition-transform duration-200"
                         :class="{ 'rotate-90': isActive({{ $category['id'] }}) }"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            @else
                <div class="w-5 h-5 flex-shrink-0 flex items-center justify-center">
                    <div class="w-1.5 h-1.5 bg-gray-400 rounded-full"></div>
                </div>
            @endif

            <a
               href="{{ route('localized.categories.show', ['locale' => app()->getLocale(), 'category' => $category['slug']]) }}"
               class="{{ $textSizeClass }} text-gray-800 hover:text-indigo-600 transition-colors truncate flex-1"
               wire:click="toggleCategory({{ $category['id'] }})">
                {{ $category['name'] }}
            </a>
        </div>

        <div class="flex items-center space-x-2 flex-shrink-0">
            @if ($category['products_count'] > 0)
                <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                    {{ $category['products_count'] }}
                </span>
            @else
                <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                    0
                </span>
            @endif
        </div>
    </div>

    @if ($category['has_children'])
        <div
             x-show="isActive({{ $category['id'] }})"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             class="mt-2 space-y-1">
            @foreach ($category['children'] as $child)
                @include('livewire.components.partials.category-accordion-node', [
                    'category' => $child,
                    'level' => $level + 1,
                ])
            @endforeach
        </div>
    @endif
</div>
