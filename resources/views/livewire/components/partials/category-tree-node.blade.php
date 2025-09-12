@props(['category', 'level' => 0])

@php
    $indentClass = match($level) {
        0 => '',
        1 => 'ml-4',
        2 => 'ml-8',
        3 => 'ml-12',
        default => 'ml-16'
    };
    
    $textSizeClass = match($level) {
        0 => 'text-sm font-medium',
        1 => 'text-sm',
        2 => 'text-xs',
        default => 'text-xs'
    };
@endphp

<div class="{{ $indentClass }}">
    <div class="flex items-center justify-between py-2 px-3 rounded-md hover:bg-gray-50 group">
        <div class="flex items-center space-x-2 flex-1 min-w-0">
            @if($category['has_children'])
                <button 
                    type="button"
                    @click="toggleNode({{ $category['id'] }})"
                    class="flex-shrink-0 w-4 h-4 flex items-center justify-center text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <svg 
                        class="w-3 h-3 transition-transform duration-200" 
                        :class="{ 'rotate-90': isOpen({{ $category['id'] }}) }"
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            @else
                <div class="w-4 h-4 flex-shrink-0"></div>
            @endif
            
            <a 
                href="{{ route('category.show', ['category' => $category['slug']]) }}"
                class="{{ $textSizeClass }} text-gray-700 hover:text-indigo-600 transition-colors truncate"
                wire:click="selectCategory({{ $category['id'] }})"
            >
                {{ $category['name'] }}
            </a>
        </div>
        
        @if($category['products_count'] > 0)
            <span class="flex-shrink-0 text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">
                {{ $category['products_count'] }}
            </span>
        @endif
    </div>
    
    @if($category['has_children'])
        <div 
            x-show="isOpen({{ $category['id'] }})" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95"
            class="mt-1"
        >
            @foreach($category['children'] as $child)
                @include('livewire.components.partials.category-tree-node', [
                    'category' => $child,
                    'level' => $level + 1
                ])
            @endforeach
        </div>
    @endif
</div>
