<?php

declare(strict_types=1);

namespace App\Providers;

use App\Filament\Components\LiveNotificationFeed;
use App\Services\DocumentService;
use App\View\Creators\CartDataCreator;
use App\View\Creators\GlobalDataCreator;
use App\View\Creators\LocalizationCreator;
use App\View\Creators\NavigationCreator;
use App\View\Creators\SeoDataCreator;
use App\View\Creators\UserDataCreator;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\ImportProducts::class,
                \App\Console\Commands\ImportPrices::class,
                \App\Console\Commands\ImportInventory::class,
            ]);
        }
    }

    public function boot(): void
    {
        // Register Livewire components
        Livewire::component('live-notification-feed', LiveNotificationFeed::class);

        // Register View Creators
        // $this->registerViewCreators();

        // Set default currency for Number helper (EUR by default)
        try {
            Number::useCurrency(config('shared.localization.default_currency', 'EUR'));
        } catch (\Throwable $e) {
            // Safe fallback if Number is unavailable
        }

        // Legacy Shopper components removed - using native Filament resources

        Model::saved(function ($model): void {
            $this->flushSitemapIfCatalog($model);
            $this->flushDiscountsIfNeeded($model);
        });
        Model::deleted(function ($model): void {
            $this->flushSitemapIfCatalog($model);
            $this->flushDiscountsIfNeeded($model);
        });

        $this->app->afterResolving(Schedule::class, function (Schedule $schedule): void {
            $schedule->command('demo:prices-multi-safe')->dailyAt('02:10')->withoutOverlapping();
            $schedule->command('demo:export-csv')->dailyAt('02:25')->withoutOverlapping();
            // Optional nightly imports if files exist
            $schedule->call(function (): void {
                $base = storage_path('app/import');
                $map = [
                    'products.csv' => 'import:products',
                    'prices.csv' => 'import:prices',
                    'inventory.csv' => 'import:inventory',
                ];
                foreach ($map as $file => $cmd) {
                    $path = $base.DIRECTORY_SEPARATOR.$file;
                    if (is_file($path)) {
                        \Artisan::call($cmd, ['path' => $path, '--chunk' => 500]);
                    }
                }
            })->dailyAt('03:00')->name('imports:nightly')->withoutOverlapping();
            $schedule->call(function (): void {
                // Rotate exports older than 7 days with timeout protection
                $timeout = now()->addMinutes(3); // 3 minute timeout for export rotation
                $disk = \Storage::disk('public');
                $dir = 'exports';
                if ($disk->exists($dir)) {
                    $files = collect($disk->files($dir))
                        ->takeUntilTimeout($timeout);
                    
                    foreach ($files as $path) {
                        $lastModified = $disk->lastModified($path);
                        if ($lastModified && $lastModified < now()->subDays(7)->getTimestamp()) {
                            $disk->delete($path);
                        }
                    }
                }
            })->dailyAt('02:40')->name('exports:rotate')->withoutOverlapping();
        });

        // Use localized Markdown templates for auth notifications
        ResetPassword::toMailUsing(function ($notifiable, string $url) {
            $locale = method_exists($notifiable, 'preferredLocale') ? ($notifiable->preferredLocale() ?: app()->getLocale()) : app()->getLocale();
            $minutes = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

            return (new MailMessage)
                ->locale($locale)
                ->subject(__('mail.reset_password_subject', [], $locale))
                ->markdown('emails.auth.password-reset', [
                    'url' => $url,
                    'minutes' => $minutes,
                ]);
        });

        VerifyEmail::toMailUsing(function ($notifiable, string $url) {
            $locale = method_exists($notifiable, 'preferredLocale') ? ($notifiable->preferredLocale() ?: app()->getLocale()) : app()->getLocale();

            return (new MailMessage)
                ->locale($locale)
                ->subject(__('mail.verify_email_subject', [], $locale))
                ->markdown('emails.auth.verify', [
                    'url' => $url,
                ]);
        });

        // Configure document service global variables for e-commerce
        $this->configureDocumentVariables();
    }

    /**
     * Register View Creators for providing data to views.
     */
    private function registerViewCreators(): void
    {
        // Global data creator - applies to all views
        View::creator('*', GlobalDataCreator::class);
        
        // Localization creator - applies to all views
        View::creator('*', LocalizationCreator::class);
        
        // User data creator - applies to all views
        View::creator('*', UserDataCreator::class);
        
        // Cart data creator - applies to all views
        View::creator('*', CartDataCreator::class);
        
        // Navigation creator - applies to specific views only
        View::creator('*', NavigationCreator::class);
        
        // SEO data creator - applies to all views
        View::creator('*', SeoDataCreator::class);
    }

    private function configureDocumentVariables(): void
    {
        $service = app(DocumentService::class);

        // Register global e-commerce variables
        config([
            'documents.global_variables' => array_merge($service->getAvailableVariables(), [
                // Company information
                '$COMPANY_NAME' => config('app.name', 'E-Commerce Store'),
                '$COMPANY_ADDRESS' => config('app.company_address', ''),
                '$COMPANY_PHONE' => config('app.company_phone', ''),
                '$COMPANY_EMAIL' => config('app.company_email', config('mail.from.address')),
                '$COMPANY_WEBSITE' => config('app.url'),
                '$COMPANY_VAT' => config('app.company_vat', ''),
                // Current date/time variables (year-month-day format)
                '$CURRENT_DATE' => now()->format(config('datetime.formats.date', 'Y-m-d')),
                '$CURRENT_DATETIME' => now()->format(config('datetime.formats.datetime_full', 'Y-m-d H:i:s')),
                '$CURRENT_YEAR' => now()->year,
                '$CURRENT_MONTH' => now()->format('F'),
                '$CURRENT_DAY' => now()->format('d'),
                // E-commerce specific variables
                '$STORE_CURRENCY' => config('app.currency', 'EUR'),
                '$STORE_LOCALE' => app()->getLocale(),
                '$STORE_TIMEZONE' => config('app.timezone'),
                // Order variables
                '$ORDER_NUMBER' => 'Order Number',
                '$ORDER_DATE' => 'Order Date',
                '$ORDER_TOTAL' => 'Order Total',
                '$ORDER_SUBTOTAL' => 'Order Subtotal',
                '$ORDER_TAX' => 'Order Tax',
                '$ORDER_SHIPPING' => 'Order Shipping',
                '$ORDER_DISCOUNT' => 'Order Discount',
                '$ORDER_STATUS' => 'Order Status',
                '$ORDER_PAYMENT_METHOD' => 'Payment Method',
                '$ORDER_SHIPPING_METHOD' => 'Shipping Method',
                // Customer variables
                '$CUSTOMER_NAME' => 'Customer Name',
                '$CUSTOMER_FIRST_NAME' => 'Customer First Name',
                '$CUSTOMER_LAST_NAME' => 'Customer Last Name',
                '$CUSTOMER_EMAIL' => 'Customer Email',
                '$CUSTOMER_PHONE' => 'Customer Phone',
                '$CUSTOMER_COMPANY' => 'Customer Company',
                '$CUSTOMER_GROUP' => 'Customer Group',
                // Address variables
                '$BILLING_ADDRESS' => 'Billing Address',
                '$BILLING_CITY' => 'Billing City',
                '$BILLING_COUNTRY' => 'Billing Country',
                '$BILLING_POSTAL_CODE' => 'Billing Postal Code',
                '$SHIPPING_ADDRESS' => 'Shipping Address',
                '$SHIPPING_CITY' => 'Shipping City',
                '$SHIPPING_COUNTRY' => 'Shipping Country',
                '$SHIPPING_POSTAL_CODE' => 'Shipping Postal Code',
                // Product variables
                '$PRODUCT_NAME' => 'Product Name',
                '$PRODUCT_SKU' => 'Product SKU',
                '$PRODUCT_PRICE' => 'Product Price',
                '$PRODUCT_DESCRIPTION' => 'Product Description',
                '$PRODUCT_BRAND' => 'Product Brand',
                '$PRODUCT_CATEGORY' => 'Product Category',
                '$PRODUCT_WEIGHT' => 'Product Weight',
                '$PRODUCT_DIMENSIONS' => 'Product Dimensions',
                // Brand and category variables
                '$BRAND_NAME' => 'Brand Name',
                '$BRAND_DESCRIPTION' => 'Brand Description',
                '$CATEGORY_NAME' => 'Category Name',
                '$CATEGORY_DESCRIPTION' => 'Category Description',
            ]),
        ]);
    }

    private function flushSitemapIfCatalog($model): void
    {
        $classes = [
            \App\Models\Product::class,
            \App\Models\Brand::class,
            \App\Models\Category::class,
            \App\Models\Collection::class,
        ];
        foreach ($classes as $class) {
            if ($model instanceof $class) {
                $locales = collect(config('app.supported_locales', 'en'))
                    ->when(fn ($v) => is_string($v), fn ($c) => collect(explode(',', (string) $c)))
                    ->map(fn ($v) => trim((string) $v))
                    ->filter()
                    ->values();
                foreach ($locales as $loc) {
                    Cache::forget("sitemap:urls:{$loc}");
                }
                break;
            }
        }
    }

    private function flushDiscountsIfNeeded($model): void
    {
        if ($model instanceof \App\Models\Discount ||
                $model instanceof \App\Models\DiscountCode ||
                $model instanceof \App\Models\DiscountCondition) {
            try {
                Cache::tags(['discounts'])->flush();
            } catch (\Throwable $e) {
            }
        }
    }
}
