{{-- Category Sidebar - Clean implementation without scrolling --}}
<div class="category-sidebar">
    {{-- Header --}}
    <div class="category-sidebar-header">
        <h3 class="category-sidebar-title">{{ __('messages.home') }}</h3>
        <p class="category-sidebar-subtitle">{{ __('messages.home') }}</p>
    </div>
    
    {{-- Categories List - No scrolling, all visible --}}
    <div class="category-sidebar-list">
        @foreach ($this->categoryTree as $category)
            <div class="category-item" data-category-id="{{ $category['id'] }}">
                @include('livewire.components.partials.category-tree-node', [
                    'category' => $category,
                    'level' => 0
                ])
            </div>
        @endforeach
    </div>
</div>