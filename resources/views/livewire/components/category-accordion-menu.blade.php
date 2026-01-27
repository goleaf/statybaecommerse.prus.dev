<div class="bg-white rounded-lg border border-gray-200 shadow-sm">
    <div class="p-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">{{ __('translations.categories') }}</h3>
        <p class="text-sm text-gray-600 mt-1">{{ __('translations.browse_all_categories') }}</p>
    </div>

    <div class="p-4">
        <div x-data="{
            activeCategory: null,
            toggleCategory(categoryId) {
                this.activeCategory = this.activeCategory === categoryId ? null : categoryId;
            },
            isActive(categoryId) {
                return this.activeCategory === categoryId;
            }
        }" class="space-y-1">

            @foreach ($this->categoryTree as $category)
                <div class="category-accordion-item">
                    @include('livewire.components.partials.category-accordion-node', [
                        'category' => $category,
                        'level' => 0,
                    ])
                </div>
            @endforeach

        </div>
    </div>
</div>
