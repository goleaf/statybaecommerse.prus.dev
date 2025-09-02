<?php

namespace App\Console\Commands;

use App\Models\Discount;
use App\Models\Product;
use App\Services\Discounts\DiscountEngine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class TestDebugbarCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debugbar:test {--show-collectors : Show available debug collectors}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Laravel Debugbar functionality and custom collectors';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('show-collectors')) {
            return $this->showCollectors();
        }

        $this->info('🔍 Testing Laravel Debugbar and Custom Collectors');
        $this->newLine();

        // Test environment
        $this->testEnvironment();

        // Test custom collectors
        $this->testDiscountCollector();
        $this->testTranslationCollector();
        $this->testLivewireCollector();
        $this->testEcommerceCollector();

        // Test helper functions
        $this->testHelperFunctions();

        $this->newLine();
        $this->info('✅ All debugbar tests completed!');
        $this->info('💡 Visit your application in the browser to see the debugbar in action.');

        return Command::SUCCESS;
    }

    protected function showCollectors(): int
    {
        $this->info('📊 Available Debug Collectors:');
        $this->newLine();

        $collectors = [
            'debugbar.discount' => 'Discount Debug Collector - Tracks discount applications, calculations, and cache operations',
            'debugbar.translation' => 'Translation Debug Collector - Monitors locale changes, translation queries, and missing translations',
            'debugbar.livewire' => 'Livewire Debug Collector - Logs component lifecycle, property updates, and method calls',
            'debugbar.ecommerce' => 'E-commerce Debug Collector - Tracks cart operations, orders, product views, and pricing',
        ];

        foreach ($collectors as $service => $description) {
            $available = app()->bound($service) ? '✅' : '❌';
            $this->line("$available <info>$service</info>");
            $this->line("   $description");
            $this->newLine();
        }

        return Command::SUCCESS;
    }

    protected function testEnvironment(): void
    {
        $this->info('🌍 Environment Check:');

        $debugEnabled = config('app.debug');
        $debugbarEnabled = config('debugbar.enabled');
        $environment = app()->environment();

        $this->line("Environment: <comment>$environment</comment>");
        $this->line('Debug Mode: ' . ($debugEnabled ? '✅ Enabled' : '❌ Disabled'));
        $this->line('Debugbar: ' . ($debugbarEnabled ? '✅ Enabled' : '❌ Disabled'));

        if (!$debugEnabled || !$debugbarEnabled) {
            $this->warn('⚠️  Debugbar may not be visible. Check APP_DEBUG and DEBUGBAR_ENABLED in .env');
        }

        $this->newLine();
    }

    protected function testDiscountCollector(): void
    {
        $this->info('💰 Testing Discount Collector:');

        if (!app()->bound('debugbar.discount')) {
            $this->error('❌ Discount collector not available');
            return;
        }

        $collector = app('debugbar.discount');

        // Test discount application logging
        $collector->logDiscountApplication('SAVE20', ['cart_total' => 100], true, 20.0);
        $collector->logDiscountApplication('INVALID', ['cart_total' => 50], false, 0);

        // Test calculation logging
        $collector->logDiscountCalculation('percentage', ['value' => 20, 'total' => 100], 20.0);

        // Test cache logging
        $collector->logCacheOperation('discount:candidates:123', true, ['discount1', 'discount2']);
        $collector->logCacheOperation('discount:candidates:456', false);

        // Test validation logging
        $collector->logDiscountValidation('EXPIRED', ['ends_at' => '2023-01-01'], false, ['Discount has expired']);

        $this->line('✅ Discount collector test completed');
        $this->newLine();
    }

    protected function testTranslationCollector(): void
    {
        $this->info('🌐 Testing Translation Collector:');

        if (!app()->bound('debugbar.translation')) {
            $this->error('❌ Translation collector not available');
            return;
        }

        $collector = app('debugbar.translation');

        // Test translation queries
        $collector->logTranslationQuery('product.name', 'en', 'Product Name', false);
        $collector->logTranslationQuery('product.description', 'lt', 'Produkto aprašymas', true);

        // Test locale changes
        $collector->logLocaleChange('en', 'lt', 'user_selection');
        $collector->logLocaleChange('lt', 'de', 'middleware');

        // Test missing translations
        $collector->logMissingTranslation('missing.key', 'de', 'English fallback');

        // Test cache operations
        $collector->logCacheOperation('put', 'translations:lt:product.name', true, 'Cached translation');
        $collector->logCacheOperation('get', 'translations:de:missing.key', false);

        $this->line('✅ Translation collector test completed');
        $this->newLine();
    }

    protected function testLivewireCollector(): void
    {
        $this->info('⚡ Testing Livewire Collector:');

        if (!app()->bound('debugbar.livewire')) {
            $this->error('❌ Livewire collector not available');
            return;
        }

        $collector = app('debugbar.livewire');

        // Test component lifecycle
        $collector->logComponentLifecycle('ProductList', 'mount', ['products' => 10]);
        $collector->logComponentLifecycle('ProductList', 'render', ['view' => 'livewire.product-list']);

        // Test property updates
        $collector->logPropertyUpdate('ProductList', 'search', '', 'laptop');
        $collector->logPropertyUpdate('ProductList', 'sortBy', 'name', 'price');

        // Test method calls
        $collector->logMethodCall('ProductList', 'addToCart', ['productId' => 123, 'quantity' => 2], 'success');

        // Test event emissions
        $collector->logEventEmission('ProductList', 'product-added-to-cart', ['productId' => 123]);

        // Test validation errors
        $collector->logValidationError('ProductForm', ['name' => ['The name field is required.']]);

        $this->line('✅ Livewire collector test completed');
        $this->newLine();
    }

    protected function testEcommerceCollector(): void
    {
        $this->info('🛒 Testing E-commerce Collector:');

        if (!app()->bound('debugbar.ecommerce')) {
            $this->error('❌ E-commerce collector not available');
            return;
        }

        $collector = app('debugbar.ecommerce');

        // Test cart operations
        $collector->logCartOperation('add', ['product_id' => 123, 'quantity' => 2, 'price' => 29.99]);
        $collector->logCartOperation('update', ['product_id' => 123, 'quantity' => 3]);

        // Test order operations
        $collector->logOrderOperation('create', 'ORD-001', ['total' => 89.97, 'items' => 3]);
        $collector->logOrderOperation('update', 'ORD-001', ['status' => 'processing']);

        // Test product views
        $collector->logProductView(123, 'awesome-laptop', ['category' => 'electronics', 'brand' => 'TechCorp']);

        // Test price calculations
        $collector->logPriceCalculation('base', ['product_id' => 123], 29.99);
        $collector->logPriceCalculation('discount', ['discount_id' => 1, 'amount' => 5.0], 24.99);
        $collector->logPriceCalculation('tax', ['rate' => 0.21, 'amount' => 24.99], 30.24);

        // Test inventory checks
        $collector->logInventoryCheck(123, 5, 10, true);
        $collector->logInventoryCheck(456, 3, 1, false);

        $this->line('✅ E-commerce collector test completed');
        $this->newLine();
    }

    protected function testHelperFunctions(): void
    {
        $this->info('🔧 Testing Helper Functions:');

        // Test debug helper functions
        if (function_exists('debug_discount')) {
            debug_discount('HELPER_TEST', ['test' => true], true, 10.0);
            $this->line('✅ debug_discount() helper working');
        }

        if (function_exists('debug_translation')) {
            debug_translation('helper.test', 'en', 'Helper Test', false);
            $this->line('✅ debug_translation() helper working');
        }

        if (function_exists('debug_livewire')) {
            debug_livewire('TestComponent', 'helper_test', ['data' => 'test']);
            $this->line('✅ debug_livewire() helper working');
        }

        if (function_exists('debug_cart')) {
            debug_cart('helper_test', ['action' => 'test']);
            $this->line('✅ debug_cart() helper working');
        }

        if (function_exists('debug_order')) {
            debug_order('helper_test', 'TEST-001', ['status' => 'test']);
            $this->line('✅ debug_order() helper working');
        }

        $this->newLine();
    }
}
