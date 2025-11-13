<div class="category-sidebar relative">
    <div class="p-4 border-b border-sage/20">
        <h3 class="text-lg font-semibold text-sage">{{ __('home.categories') }}</h3>
        <p class="text-sm text-sage/70 mt-1">{{ __('home.browse_categories') }}</p>
    </div>
    
    <div class="sidebar-content flex-1 overflow-y-auto overflow-x-visible">
            @foreach ($this->categoryTree as $category)
            <div class="category-item relative" data-category-id="{{ $category['id'] }}">
                    @include('livewire.components.partials.category-tree-node', [
                        'category' => $category,
                        'level' => 0
                    ])
                </div>
            @endforeach
    </div>
</div>
