<div class="bg-white rounded-lg border border-gray-200 shadow-sm">
    <div class="p-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">{{ __('home.categories') }}</h3>
        <p class="text-sm text-gray-600 mt-1">{{ __('home.browse_categories') }}</p>
    </div>
    
    <div class="p-4">
        <div x-data="{ 
            openNodes: new Set(),
            toggleNode(nodeId) {
                if (this.openNodes.has(nodeId)) {
                    this.openNodes.delete(nodeId);
                } else {
                    this.openNodes.add(nodeId);
                }
            },
            isOpen(nodeId) {
                return this.openNodes.has(nodeId);
            }
        }" class="space-y-1">
            
            @foreach ($this->categoryTree as $category)
                <div class="category-item">
                    @include('livewire.components.partials.category-tree-node', [
                        'category' => $category,
                        'level' => 0
                    ])
                </div>
            @endforeach
            
        </div>
    </div>
</div>
