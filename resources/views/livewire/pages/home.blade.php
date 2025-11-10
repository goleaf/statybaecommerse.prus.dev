<main class="bg-sage text-gray-900" aria-label="{{ __('frontend/home.homepage') }}">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 py-8 lg:grid-cols-4">
            <aside class="lg:col-span-1">
                <div class="sticky top-4 space-y-6">
                    <livewire:components.category-sidebar />
                </div>
            </aside>

            <div class="lg:col-span-3 space-y-8">
                <section class="relative">
                    <livewire:home-slider />
                </section>

                <x-home.mission-loyalty />

                <x-home.hero-stats :stats="$stats ?? []" />

                <section class="relative">
                    <livewire:home.product-shelf
                        :preset="'featured'"
                        :limit="8"
                        :title="__('frontend/home.products.sections.featured.title')"
                        :subtitle="__('frontend/home.products.sections.featured.subtitle')"
                    />
                </section>

                <section class="relative space-y-20">
                    <livewire:home.product-shelf
                        :preset="'latest'"
                        :limit="8"
                        :title="__('frontend/home.products.sections.latest.title')"
                        :subtitle="__('frontend/home.products.sections.latest.subtitle')"
                    />
                    <livewire:home.product-shelf
                        :preset="'trending'"
                        :limit="8"
                        :title="__('frontend/home.products.sections.trending.title')"
                        :subtitle="__('frontend/home.products.sections.trending.subtitle')"
                    />
                    <livewire:home.product-shelf
                        :preset="'sale'"
                        :limit="12"
                        :title="__('frontend/home.products.sections.sale.title')"
                        :subtitle="__('frontend/home.products.sections.sale.subtitle')"
                    />
                </section>

                <section class="relative">
                    <livewire:home.collections-showcase />
                </section>
            </div>
        </div>
    </div>
</main>
