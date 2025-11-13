@props(['category', 'level' => 0])

@php
    $hasChildren = !empty($category['children']) && count($category['children']) > 0;
    $categoryUrl = route('localized.categories.show', [
        'locale' => app()->getLocale(),
        'category' => $category['slug']
    ]);
@endphp

@if($level === 0)
    {{-- Level 0: Main category link in sidebar --}}
    <a 
        href="{{ $categoryUrl }}"
        class="category-link block px-4 py-3 text-sm font-semibold text-sage transition-all hover:bg-sage/10 hover:text-white {{ $hasChildren ? 'has-children' : '' }}"
        wire:click="selectCategory({{ $category['id'] }})"
    >
        <div class="level-0-category-content flex items-center justify-between gap-2">
            <span class="level-0-category-name truncate flex-1">{{ $category['name'] }}</span>
            @if(isset($category['products_count']) && $category['products_count'] > 0)
                <span class="level-0-category-count text-xs bg-sage/10 text-sage/80 px-2 py-0.5 rounded font-medium flex-shrink-0">
                    {{ $category['products_count'] }}
                </span>
            @endif
            @if($hasChildren)
                <svg class="level-0-category-arrow w-3 h-3 text-sage/50 transition-transform flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
            @endif
        </div>
    </a>
    
    @if($hasChildren)
        {{-- Level 2 Popup Panel - appears on hover of .category-item --}}
        <div class="level-2-popup-panel">
            <div class="panel-header">
                <a href="{{ $categoryUrl }}" class="panel-title hover:text-white transition-colors block">
                {{ $category['name'] }}
            </a>
                @if(isset($category['description']) && $category['description'])
                    <span class="panel-subtitle">{{ Str::limit($category['description'], 50) }}</span>
                @else
                    <span class="panel-subtitle">{{ __('home.browse_categories') }}</span>
                @endif
            </div>
            <div class="level-2-categories-list">
                @foreach($category['children'] as $child)
                    @php
                        $childHasChildren = !empty($child['children']) && count($child['children']) > 0;
                        $childUrl = route('localized.categories.show', ['locale' => app()->getLocale(), 'category' => $child['slug']]);
                    @endphp
                    <div class="level-2-category-item-wrapper">
                        <a 
                            href="{{ $childUrl }}"
                            class="level-2-category-item {{ $childHasChildren ? 'has-children' : '' }}"
                            wire:click="selectCategory({{ $child['id'] }})"
                        >
                            <div class="level-2-category-content flex items-center justify-between gap-2">
                                <span class="level-2-category-name truncate flex-1">{{ $child['name'] }}</span>
                                @if(isset($child['products_count']) && $child['products_count'] > 0)
                                    <span class="level-2-category-count text-xs bg-sage/10 text-sage/80 px-2 py-0.5 rounded font-medium flex-shrink-0">
                                        {{ $child['products_count'] }}
                                    </span>
                                @endif
                                @if($childHasChildren)
                                    <svg class="level-2-category-arrow w-3 h-3 text-sage/50 transition-transform flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                @endif
        </div>
                        </a>
                        
                        @if($childHasChildren)
                            {{-- Level 3 Popup Panel - appears on hover of .level-2-category-item-wrapper --}}
                            <div class="level-3-popup-panel">
                                <div class="panel-header">
                                    <a href="{{ $childUrl }}" class="panel-title hover:text-white transition-colors block">
                                        {{ $child['name'] }}
                                    </a>
                                    @if(isset($child['description']) && $child['description'])
                                        <span class="panel-subtitle">{{ Str::limit($child['description'], 50) }}</span>
                                    @else
                                        <span class="panel-subtitle">{{ __('home.browse_categories') }}</span>
                                    @endif
                                </div>
                                <div class="level-3-categories-list">
                                    @foreach($child['children'] as $grandchild)
                                        @php
                                            $grandchildUrl = route('localized.categories.show', ['locale' => app()->getLocale(), 'category' => $grandchild['slug']]);
                                        @endphp
                                        <div class="level-3-category-item-wrapper">
                                            <a 
                                                href="{{ $grandchildUrl }}"
                                                class="level-3-category-item"
                                                wire:click="selectCategory({{ $grandchild['id'] }})"
                                            >
                                                <div class="level-3-category-content flex items-center justify-between gap-2">
                                                    <span class="level-3-category-name truncate flex-1">{{ $grandchild['name'] }}</span>
                                                    @if(isset($grandchild['products_count']) && $grandchild['products_count'] > 0)
                                                        <span class="level-3-category-count text-xs bg-sage/10 text-sage/80 px-2 py-0.5 rounded font-medium flex-shrink-0">
                                                            {{ $grandchild['products_count'] }}
            </span>
        @endif
    </div>
                                            </a>
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
@endif

