@props(['category', 'level' => 0])

@php
    $textSizeClass = match($level) {
        0 => 'text-sm font-medium',
        1 => 'text-sm',
        2 => 'text-xs',
        3 => 'text-xs',
        default => 'text-xs'
    };
    
    $paddingClass = match($level) {
        0 => 'py-2 px-4',
        1 => 'py-1.5 px-4',
        2 => 'py-1 px-4',
        3 => 'py-1 px-4',
        default => 'py-1 px-4'
    };
    
    $indentClass = match($level) {
        0 => 'pl-4',
        1 => 'pl-6',
        2 => 'pl-8',
        3 => 'pl-10',
        default => 'pl-10'
    };
@endphp

<div class="category-tree-node-wrapper">
    @if($level === 0)
        {{-- Level 1: Main category --}}
        <div class="category-item category-node flex items-center justify-between {{ $paddingClass }} hover:bg-sage/10 group transition-all duration-200 border-b border-sage/10">
            <div class="flex items-center space-x-2 flex-1 min-w-0">
                @if($category['has_children'])
                    <div class="flex-shrink-0 w-4 h-4 flex items-center justify-center">
                        <svg class="w-3 h-3 text-sage/70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                @else
                    <div class="w-4 h-4 flex-shrink-0 flex items-center justify-center">
                        <div class="w-1.5 h-1.5 bg-sage/50 rounded-full"></div>
                    </div>
                @endif
                
                <a 
                    href="{{ route('localized.categories.show', ['locale' => app()->getLocale(), 'category' => $category['slug']]) }}"
                    class="{{ $textSizeClass }} text-sage hover:text-sage/80 transition-colors truncate font-montserrat"
                    wire:click="selectCategory({{ $category['id'] }})"
                >
                    {{ $category['name'] }}
                </a>
            </div>
            
            @if($category['products_count'] > 0)
                <span class="flex-shrink-0 text-xs text-sage/70 bg-sage/10 hover:bg-sage/20 px-2 py-0.5 rounded font-medium transition-colors duration-200">
                    {{ $category['products_count'] }}
                </span>
            @endif
        </div>
        
        {{-- Level 2 & 3: Popup panels on the right --}}
        @if(isset($category['has_children']) && $category['has_children'] && isset($category['children']) && count($category['children']) > 0)
            <div class="level-2-popup-panel">
                <div class="panel-header">
                    <span class="panel-title">{{ $category['name'] }}</span>
                    <span class="panel-subtitle">{{ __('subcategories') }}</span>
                </div>
                <div class="level-2-categories-list">
                    @foreach($category['children'] as $child)
                        <div class="level-2-category-item-wrapper">
                            <a 
                                href="{{ route('localized.categories.show', ['locale' => app()->getLocale(), 'category' => $child['slug']]) }}"
                                class="level-2-category-item {{ $child['has_children'] ? 'has-children' : '' }}"
                                wire:click="selectCategory({{ $child['id'] }})"
                            >
                                <div class="level-2-category-content">
                                    <span class="level-2-category-name">{{ $child['name'] }}</span>
                                    @if(isset($child['products_count']) && $child['products_count'] > 0)
                                        <span class="level-2-category-count">
                                            {{ $child['products_count'] }}
                                        </span>
                                    @endif
                                    @if($child['has_children'])
                                        <svg class="level-2-category-arrow" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                        </svg>
                                    @endif
                                </div>
                            </a>
                            
                            {{-- Level 3 subcategories - popup panel on the right --}}
                            @if($child['has_children'] && isset($child['children']) && count($child['children']) > 0)
                                <div class="level-3-popup-panel">
                                    <div class="panel-header">
                                        <span class="panel-title">{{ $child['name'] }}</span>
                                        <span class="panel-subtitle">{{ __('subcategories') }}</span>
                                    </div>
                                    <div class="level-3-categories-list">
                                        @foreach($child['children'] as $grandChild)
                                            <div class="level-3-category-item-wrapper">
                                                <a 
                                                    href="{{ route('localized.categories.show', ['locale' => app()->getLocale(), 'category' => $grandChild['slug']]) }}"
                                                    class="level-3-category-item {{ $grandChild['has_children'] ? 'has-children' : '' }}"
                                                    wire:click="selectCategory({{ $grandChild['id'] }})"
                                                >
                                                    <div class="level-3-category-content">
                                                        <span class="level-3-category-name">{{ $grandChild['name'] }}</span>
                                                        @if(isset($grandChild['products_count']) && $grandChild['products_count'] > 0)
                                                            <span class="level-3-category-count">
                                                                {{ $grandChild['products_count'] }}
                                                            </span>
                                                        @endif
                                                        @if($grandChild['has_children'])
                                                            <svg class="level-3-category-arrow" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                                            </svg>
                                                        @endif
                                                    </div>
                                                </a>
                                                
                                                {{-- Level 4 subcategories - separate hover panel --}}
                                                @if($grandChild['has_children'] && isset($grandChild['children']) && count($grandChild['children']) > 0)
                                                    <div class="sub-sub-subcategories-panel">
                                                        <div class="panel-header">
                                                            <span class="panel-title">{{ $grandChild['name'] }}</span>
                                                            <span class="panel-subtitle">{{ __('subcategories') }}</span>
                                                        </div>
                                                        <div class="sub-sub-subcategories-list">
                                                            @foreach($grandChild['children'] as $greatGrandChild)
                                                                <a 
                                                                    href="{{ route('localized.categories.show', ['locale' => app()->getLocale(), 'category' => $greatGrandChild['slug']]) }}"
                                                                    class="sub-sub-subcategory-item"
                                                                    wire:click="selectCategory({{ $greatGrandChild['id'] }})"
                                                                >
                                                                    <div class="sub-sub-subcategory-content">
                                                                        <span class="sub-sub-subcategory-name">{{ $greatGrandChild['name'] }}</span>
                                                                        @if(isset($greatGrandChild['products_count']) && $greatGrandChild['products_count'] > 0)
                                                                            <span class="sub-sub-subcategory-count">
                                                                                {{ $greatGrandChild['products_count'] }}
                                                                            </span>
                                                                        @endif
                                                                    </div>
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @else
        {{-- Subcategory item (for nested display if needed) --}}
        <div class="category-node flex items-center justify-between {{ $paddingClass }} hover:bg-sage/10 group transition-all duration-200">
            <div class="flex items-center space-x-2 flex-1 min-w-0">
                <div class="w-4 h-4 flex-shrink-0 flex items-center justify-center">
                    <div class="w-1.5 h-1.5 bg-sage/50 rounded-full"></div>
                </div>
                
                <a 
                    href="{{ route('localized.categories.show', ['locale' => app()->getLocale(), 'category' => $category['slug']]) }}"
                    class="{{ $textSizeClass }} text-sage hover:text-sage/80 transition-colors truncate font-montserrat"
                    wire:click="selectCategory({{ $category['id'] }})"
                >
                    {{ $category['name'] }}
                </a>
            </div>
            
            @if($category['products_count'] > 0)
                <span class="flex-shrink-0 text-xs text-sage/70 bg-sage/10 hover:bg-sage/20 px-2 py-0.5 rounded font-medium transition-colors duration-200">
                    {{ $category['products_count'] }}
                </span>
            @endif
        </div>
    @endif
    
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
