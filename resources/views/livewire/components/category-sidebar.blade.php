{{-- Category Sidebar - Clean implementation without scrolling --}}
<div class="category-sidebar">
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
