@props(['category', 'level' => 0])

@php
    $hasChildren = !empty($category['children']) && count($category['children']) > 0;
    $categoryUrl = route('frontend.categories.show', [
        'category' => $category['slug']
    ]);
@endphp

@if($level === 0)
    {{-- Level 0: Main category link in sidebar --}}
    <a 
        href="{{ $categoryUrl }}"
        class="category-link level-0-link {{ $hasChildren ? 'has-children' : '' }}"
        wire:click="selectCategory({{ $category['id'] }})"
    >
        <div class="category-link-content">
            <span class="category-name">{{ $category['name'] }}</span>
            @if(isset($category['products_count']) && $category['products_count'] > 0)
                <span class="category-count">{{ $category['products_count'] }}</span>
            @endif
            @if($hasChildren)
                <svg class="category-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            @endif
        </div>
    </a>
    
    @if($hasChildren)
        {{-- Level 2 Popup Panel - appears on hover of .category-item --}}
        <div class="popup-panel level-2-panel">
            <div class="popup-header">
                <a href="{{ $categoryUrl }}" class="popup-title">
                    {{ $category['name'] }}
                </a>
                @if(isset($category['description']) && $category['description'])
                    <span class="popup-subtitle">{{ Str::limit($category['description'], 50) }}</span>
                @else
                    <span class="popup-subtitle">{{ __('messages.home') }}</span>
                @endif
            </div>
            <div class="popup-list level-2-list">
                @foreach($category['children'] as $child)
                    @php
                        $childHasChildren = !empty($child['children']) && count($child['children']) > 0;
                        $childUrl = route('frontend.categories.show', ['category' => $child['slug']]);
                    @endphp
                    <div class="popup-item-wrapper level-2-wrapper">
                        <a 
                            href="{{ $childUrl }}"
                            class="popup-item level-2-item {{ $childHasChildren ? 'has-children' : '' }}"
                            wire:click="selectCategory({{ $child['id'] }})"
                        >
                            <div class="popup-item-content">
                                <span class="popup-item-name">{{ $child['name'] }}</span>
                                @if(isset($child['products_count']) && $child['products_count'] > 0)
                                    <span class="popup-item-count">{{ $child['products_count'] }}</span>
                                @endif
                                @if($childHasChildren)
                                    <svg class="popup-item-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                @endif
                            </div>
                        </a>
                        
                        @if($childHasChildren)
                            {{-- Level 3 Popup Panel - appears on hover of .level-2-wrapper --}}
                            <div class="popup-panel level-3-panel">
                                <div class="popup-header">
                                    <a href="{{ $childUrl }}" class="popup-title">
                                        {{ $child['name'] }}
                                    </a>
                                    @if(isset($child['description']) && $child['description'])
                                        <span class="popup-subtitle">{{ Str::limit($child['description'], 50) }}</span>
                                    @else
                                        <span class="popup-subtitle">{{ __('messages.home') }}</span>
                                    @endif
                                </div>
                                <div class="popup-list level-3-list">
                                    @foreach($child['children'] as $grandchild)
                                        @php
                                            $grandchildUrl = route('frontend.categories.show', ['category' => $grandchild['slug']]);
                                        @endphp
                                        <div class="popup-item-wrapper level-3-wrapper">
                                            <a 
                                                href="{{ $grandchildUrl }}"
                                                class="popup-item level-3-item"
                                                wire:click="selectCategory({{ $grandchild['id'] }})"
                                            >
                                                <div class="popup-item-content">
                                                    <span class="popup-item-name">{{ $grandchild['name'] }}</span>
                                                    @if(isset($grandchild['products_count']) && $grandchild['products_count'] > 0)
                                                        <span class="popup-item-count">{{ $grandchild['products_count'] }}</span>
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

