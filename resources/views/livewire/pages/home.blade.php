<div class="bg-gray-50 text-gray-900">
    <!-- Slider Section -->
    <livewire:home-slider />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-shared.sidebar-layout>
            <x-slot name="sidebar">
                <livewire:components.category-sidebar />
            </x-slot>
            
            <div class="space-y-20">
                <livewire:home.categories-showcase />
                <livewire:home.collections-showcase />

                <livewire:home.product-shelf :preset="'featured'" :limit="8" />
                <livewire:home.product-shelf :preset="'latest'" :limit="8" />
                <livewire:home.product-shelf :preset="'sale'" :limit="12" :title="__('home.sale_products')" :subtitle="__('home.sale_products_subtitle')" />
            </div>
        </x-shared.sidebar-layout>
    </div>

</div>
