<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Shared\CacheService;
use App\Services\Shared\ProductService;
use App\Services\Shared\TranslationService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

final class SharedComponentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register shared services as singletons
        $this->app->singleton(CacheService::class);
        $this->app->singleton(TranslationService::class);
        $this->app->singleton(ProductService::class);
    }

    public function boot(): void
    {
        // Register custom Blade directives
        $this->registerBladeDirectives();

        // Register view composers
        $this->registerViewComposers();
    }

    private function registerBladeDirectives(): void
    {
        // @price directive for formatting prices
        Blade::directive('price', function ($expression) {
            return "<?php echo App\Support\Helpers\SharedHelpers::formatPrice({$expression}); ?>";
        });

        // @date directive for formatting dates
        Blade::directive('date', function ($expression) {
            return "<?php echo App\Support\Helpers\SharedHelpers::formatDate({$expression}); ?>";
        });

        // @seo directive for SEO meta tags
        Blade::directive('seo', function ($expression) {
            return "<?php 
                \$seoData = {$expression};
                echo '<title>' . App\Support\Helpers\SharedHelpers::getSeoTitle(\$seoData['title'] ?? '') . '</title>';
                echo '<meta name=\"description\" content=\"' . App\Support\Helpers\SharedHelpers::getSeoDescription(\$seoData['description'] ?? '') . '\">';
                if (isset(\$seoData['keywords'])) {
                    echo '<meta name=\"keywords\" content=\"' . \$seoData['keywords'] . '\">';
                }
            ?>";
        });

        // @trans directive for translations with shared service
        Blade::directive('trans', function ($expression) {
            return "<?php echo app(App\Services\Shared\TranslationService::class)->getTranslation({$expression}); ?>";
        });

        // @currency directive for current currency
        Blade::directive('currency', function () {
            return "<?php echo app(App\Services\Shared\TranslationService::class)->getCurrentCurrency(); ?>";
        });

        // @rtl directive for RTL languages
        Blade::directive('rtl', function () {
            return "<?php echo App\Support\Helpers\SharedHelpers::isRtlLocale() ? 'dir=\"rtl\"' : ''; ?>";
        });

        // @truncate directive for text truncation
        Blade::directive('truncate', function ($expression) {
            return "<?php echo App\Support\Helpers\SharedHelpers::truncateText({$expression}); ?>";
        });

        // @slug directive for generating slugs
        Blade::directive('slug', function ($expression) {
            return "<?php echo App\Support\Helpers\SharedHelpers::generateSlug({$expression}); ?>";
        });
    }

    private function registerViewComposers(): void
    {
        // Share common data with all views
        view()->composer('*', function ($view) {
            $view->with([
                'currentLocale' => app()->getLocale(),
                'currentCurrency' => current_currency(),
                'supportedLocales' => config('shared.localization.supported_locales', ['lt', 'en']),
                'cartCount' => $this->getCartCount(),
            ]);
        });

        // Share navigation data
        view()->composer(['components.layouts.header', 'livewire.components.enhanced-navigation'], function ($view) {
            $view->with([
                'topCategories' => $this->getTopCategories(),
                'featuredBrands' => $this->getFeaturedBrands(),
            ]);
        });
    }

    private function getCartCount(): int
    {
        $cart = session('cart', []);

        return array_sum(array_column($cart, 'quantity'));
    }

    private function getTopCategories()
    {
        return app(CacheService::class)->rememberLong(
            'navigation.top_categories.'.app()->getLocale(),
            fn () => \App\Models\Category::query()
                ->with(['translations' => function ($q) {
                    $q->where('locale', app()->getLocale());
                }])
                ->where('is_visible', true)
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->limit(6)
                ->get()
        );
    }

    private function getFeaturedBrands()
    {
        return app(CacheService::class)->rememberLong(
            'navigation.featured_brands.'.app()->getLocale(),
            fn () => \App\Models\Brand::query()
                ->with(['translations' => function ($q) {
                    $q->where('locale', app()->getLocale());
                }])
                ->where('is_enabled', true)
                ->where('is_featured', true)
                ->orderBy('sort_order')
                ->limit(6)
                ->get()
        );
    }
}
