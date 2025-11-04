<main class="bg-sage text-gray-900" aria-label="{{ __('frontend/home.homepage') }}">
    <!-- Two Column Layout: Categories + Main Content with Slider -->
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 py-8">
            <!-- Left Sidebar: Category Sidebar Only -->
            <aside class="lg:col-span-1">
                <div class="sticky top-4 space-y-6">
                    <livewire:components.category-sidebar />
                </div>
            </aside>

            <!-- Right Main Content with Slider -->
            <div class="lg:col-span-3 space-y-8">
                <!-- Hero / Slider -->
                <section class="relative">
                    <livewire:home-slider />
                </section>

                <x-home.mission-loyalty />

                <!-- Featured Shelf (primary) -->
                <section class="relative">
                    <livewire:home.product-shelf :preset="'featured'" :limit="8" :title="__('frontend/home.products.sections.featured.title')" />
                </section>
                

                <!-- Shelves: Latest / Trending / Sale -->
                <section class="relative space-y-20">
                    <livewire:home.product-shelf :preset="'latest'" :limit="8" :title="__('frontend/home.products.sections.latest.title')" />
                    <livewire:home.product-shelf :preset="'trending'" :limit="8" :title="__('frontend/home.products.sections.trending.title')" />
                    <livewire:home.product-shelf :preset="'sale'" :limit="12" :title="__('frontend/home.products.sections.sale.title')" />
                </section>
            </div>
        </div>
    </div>

    <!-- Full Catalogue Explorer -->
 

</main>
