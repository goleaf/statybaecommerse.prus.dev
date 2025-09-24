<main class="bg-gray-50 text-gray-900" aria-label="{{ __('home.homepage') }}">
    <!-- Hero / Slider -->
    <section class="relative">
        <livewire:home-slider />
    </section>

    <!-- Featured Shelf (primary) -->
    <section class="relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <livewire:home.product-shelf :preset="'featured'" :limit="8" :title="'Išskirtiniai produktai'" />
        </div>
    </section>

    <!-- Collections Showcase -->
    <section class="relative py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 gap-8">
            <div>
                <livewire:home.collections-showcase />
            </div>
        </div>
    </section>

    <!-- Shelves: Latest / Trending / Sale -->
    <section class="relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-20">
            <livewire:home.product-shelf :preset="'latest'" :limit="8" :title="'Išskirtiniai produktai'" />
            <livewire:home.product-shelf :preset="'trending'" :limit="8" :title="'Išskirtiniai produktai'" />
            <livewire:home.product-shelf :preset="'sale'" :limit="12" :title="'Išskirtiniai produktai'" />
        </div>
    </section>

    <!-- Full Catalogue Explorer -->
    <section class="relative bg-slate-950 text-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <livewire:home.product-catalogue />
        </div>
    </section>

</main>
