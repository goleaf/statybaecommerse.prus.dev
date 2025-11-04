{{-- Clear Navigation Category Sidebar with Hover Subcategories --}}
<div class="category-sidebar bg-dark text-sage rounded-lg overflow-visible relative h-full">
    {{-- Header Section --}}
    <div class="sidebar-header bg-dark/90 p-4 border-b border-sage/20">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-sage/20 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-sage" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-sage font-montserrat">{{ __('home.categories') }}</h3>
            </div>
        </div>
    </div>
    
    {{-- Categories Tree - Full Height, No Scroll --}}
    <div class="sidebar-content flex-1">
        <div class="space-y-1">
            @foreach ($this->categoryTree as $category)
                <div class="category-item group relative">
                    @include('livewire.components.partials.category-tree-node', [
                        'category' => $category,
                        'level' => 0
                    ])
                </div>
            @endforeach
        </div>
    </div>
</div>
