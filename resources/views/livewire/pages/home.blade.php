<main class="bg-sage text-gray-900" aria-label="{{ __('messages.home_homepage') }}">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 py-8 lg:grid-cols-4">
            <aside class="lg:col-span-1">
                <div class="home-category-sidebar space-y-4 z-30 lg:sticky lg:top-4">
                    <livewire:components.category-sidebar />
                </div>
            </aside>

            <div class="lg:col-span-3 space-y-8">
                <section class="relative">
                    <livewire:home-slider />
                </section>

                <section class="relative">
                    <livewire:home.product-shelf
                        :preset="'featured'"
                        :limit="8"
                        :title="__('messages.home_products_featured_title')"
                        :subtitle="__('messages.home_products_featured_subtitle')"
                    />
                </section>

                <section class="relative space-y-20">
                    <livewire:home.product-shelf
                        :preset="'latest'"
                        :limit="8"
                        :title="__('messages.home_products_latest_title')"
                        :subtitle="__('messages.home_products_latest_subtitle')"
                    />
                    <livewire:home.product-shelf
                        :preset="'trending'"
                        :limit="8"
                        :title="__('messages.home_products_trending_title')"
                        :subtitle="__('messages.home_products_trending_subtitle')"
                    />
                    <livewire:home.product-shelf
                        :preset="'sale'"
                        :limit="12"
                        :title="__('messages.home_products_sale_title')"
                        :subtitle="__('messages.home_products_sale_subtitle')"
                    />
                </section>

                <section class="relative">
                    <livewire:home.collections-showcase />
                </section>
            </div>
        </div>
    </div>
</main>
