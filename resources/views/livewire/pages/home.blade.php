@php
    // Ensure locale is set from route before rendering (mirror SetLocale middleware logic)
    $request = request();
    $supportedConfig = config('app.supported_locales', 'lt,en');
    $supportedLocales = is_array($supportedConfig)
        ? $supportedConfig
        : array_map('trim', explode(',', (string) $supportedConfig));
    $supportedLocales = array_values(array_filter($supportedLocales, function($locale) {
        return is_string($locale) && $locale !== '';
    }));

    $routeLocale = $request->route('locale');
    $locale = $routeLocale;

    // If no route parameter or invalid, try session, cookie, or default
    if (!$locale || !in_array($locale, $supportedLocales, true)) {
        $candidateLocales = array_filter([
            session('locale'),
            session('app.locale'),
            $request->cookie('app_locale'),
            config('app.locale', 'lt'),
        ], function($candidate) {
            return is_string($candidate) && $candidate !== '';
        });

        foreach ($candidateLocales as $candidate) {
            if (in_array($candidate, $supportedLocales, true)) {
                $locale = $candidate;
                break;
            }
        }
    }

    // Ensure we have a valid locale
    if (!$locale || !in_array($locale, $supportedLocales, true)) {
        $locale = config('app.locale', 'lt');
    }

    // Set the locale explicitly
    app()->setLocale($locale);
    app()->instance('request_locale', $locale);
@endphp

<main class="bg-sage text-gray-900" aria-label="{{ __('messages.home_homepage') }}">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 py-8 lg:grid-cols-4">
            <aside class="lg:col-span-1">
                <div class="sticky top-4 space-y-6 z-30">
                    <livewire:components.category-sidebar />
                </div>
            </aside>

            <div class="lg:col-span-3 space-y-8">
                <section class="relative">
                    <livewire:home-slider />
                </section>

                <x-home.mission-loyalty />

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
