<?php

declare(strict_types=1);

namespace App\Providers;

use App\Console\Commands\ProfiledSeedCommand;
use App\Contracts\DocumentServiceContract;
use App\Contracts\HealthReporter as HealthReporterContract;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Filament\Components\LiveNotificationFeed;
use App\Infrastructure\Product\Repositories\EloquentProductRepository;
use App\Models\DiscountCode;
use App\Models\DiscountRedemption;
use App\Models\Document;
use App\Models\EmailCampaign;
use App\Models\FeatureFlag;
use App\Models\SystemSetting;
use App\Observers\UserAttributionObserver;
use App\Services\CacheInvalidationService;
use App\Services\DocumentService;
use App\Support\Filament\SearchableComponentHelper;
use App\Support\Filesystem\GracefulFilesystem;
use App\Support\Health\HealthReporter;
use App\Support\Html\HtmlSanitizer;
use App\Support\Security\CspNonce;
use App\Support\Storage\SecureStorage;
use App\Support\Tracing\Trace;
use App\Support\Tracing\TraceContext;
use App\Support\Uploads\SecureUploadHandler;
use App\View\Creators\CartDataCreator;
use App\View\Creators\GlobalDataCreator;
use App\View\Creators\LocalizationCreator;
use App\View\Creators\NavigationCreator;
use App\View\Creators\SeoDataCreator;
use App\View\Creators\UserDataCreator;
use DateInterval;
use DateTimeInterface;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Filament\Tables\Testing\TestsActions;
use Filament\Tables\Testing\TestsBulkActions;
use Filament\Tables\Testing\TestsColumns;
use Filament\Tables\Testing\TestsFilters;
use Filament\Tables\Testing\TestsRecords;
use Filament\Tables\Testing\TestsSummaries;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;

use function in_array;

use InvalidArgumentException;

use function is_array;

use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(HealthReporterContract::class, HealthReporter::class);
        $this->app->bind(DocumentServiceContract::class, DocumentService::class);

        // Share a single sanitizer instance so every consumer reuses the same allow-list configuration.
        $this->app->singleton(HtmlSanitizer::class, static fn (): HtmlSanitizer => new HtmlSanitizer);

        // Scope CSP nonces per request so all downstream consumers reference the same token.
        $this->app->scoped(CspNonce::class, static fn (): CspNonce => new CspNonce);

        // Replace the default filesystem binding with the graceful shim for deterministic backup tests.
        $this->app->singleton(Filesystem::class, static fn (): Filesystem => new GracefulFilesystem);
        $this->app->alias(Filesystem::class, 'files');

        if ($this->app->runningInConsole()) {
            // Register import utilities and override the core db:seed command with a profiled variant.
            $this->commands([
                \App\Console\Commands\ImportProducts::class,
                \App\Console\Commands\ImportPrices::class,
                \App\Console\Commands\ImportInventory::class,
                ProfiledSeedCommand::class,
            ]);

            $this->app->extend('command.db.seed', function ($command, $app) {
                /** @var Dispatcher|null $dispatcher */
                $dispatcher = $app->bound('events') ? $app->make('events') : null;

                return new ProfiledSeedCommand($app->make('db'), $dispatcher, $app->make('config'));
            });
        }

        // Bind the domain-level product repository to its Eloquent implementation.
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
    }

    public function boot(): void
    {
        if (class_exists(\Illuminate\Foundation\Vite::class) && method_exists(\Illuminate\Foundation\Vite::class, 'useCspNonce')) {
            Vite::useCspNonce(fn (): string => csp_nonce());
        }

        if (method_exists(Livewire::class, 'setScriptNonce')) {
            Livewire::setScriptNonce(fn (): string => csp_nonce());
        }

        $this->registerModelObservers();
        $this->registerQueueMonitoring();

        $this->registerCollectionTimeoutMacros();

        $this->registerQueueTracing();

        $this->registerSearchableInputMacros();

        // Expose the bespoke Filament widget tab views as anonymous Blade components for reuse across resources.
        Blade::anonymousComponentPath(resource_path('views/filament/components'), 'filament.components');

        // Manually register Filament table testing helpers to keep compatibility with the upgraded packages.
        Testable::mixin(new TestsFilters);
        Testable::mixin(new TestsActions);
        Testable::mixin(new TestsBulkActions);
        Testable::mixin(new TestsColumns);
        Testable::mixin(new TestsRecords);
        Testable::mixin(new TestsSummaries);

        // Register Livewire components
        Livewire::component('live-notification-feed', LiveNotificationFeed::class);

        if (method_exists(Livewire::class, 'useCspNonce')) {
            // Provide the same nonce helper to Livewire so inline hooks honour the CSP.
            Livewire::useCspNonce(static fn (): string => csp_nonce());
        }

        if (method_exists(Vite::class, 'useCspNonce')) {
            // Ensure generated asset tags from Vite inherit the request-specific nonce value.
            Vite::useCspNonce(static fn (): string => csp_nonce());
        }

        Blade::anonymousComponentNamespace(
            resource_path('views/filament/components'),
            'filament'
        ); // Expose custom Filament Blade components for anonymous <x-filament::*> usage.

        if (! Testable::hasMacro('assertCanSeeFormData')) {
            Testable::macro('assertCanSeeFormData', function (array $data): Testable {
                foreach (Arr::dot($data) as $value) {
                    if (is_scalar($value) && $value !== null) {
                        $this->assertSee((string) $value, escape: false);
                    }
                }

                return $this;
            });
        }

        if (! Testable::hasMacro('assertCanSeeText')) {
            // Provide a convenience assertion that respects unescaped content, matching Filament v3's testing API.
            Testable::macro('assertCanSeeText', function (string $text): Testable {
                $this->assertSee($text, escape: false);

                return $this;
            });
        }

        if (! class_exists(\Filament\Forms\Form::class) && class_exists(\Filament\Schemas\Schema::class)) {
            class_alias(\Filament\Schemas\Schema::class, \Filament\Forms\Form::class);
        }

        if (! class_exists(\Filament\Forms\Components\Section::class) && class_exists(\Filament\Schemas\Components\Section::class)) {
            class_alias(\Filament\Schemas\Components\Section::class, \Filament\Forms\Components\Section::class);
        }

        if (! class_exists(\Filament\Forms\Components\Grid::class) && class_exists(\Filament\Schemas\Components\Grid::class)) {
            class_alias(\Filament\Schemas\Components\Grid::class, \Filament\Forms\Components\Grid::class);
        }

        if (! class_exists(\Filament\Forms\Get::class) && class_exists(\Filament\Schemas\Components\Utilities\Get::class)) {
            class_alias(\Filament\Schemas\Components\Utilities\Get::class, \Filament\Forms\Get::class);
        }

        if (! class_exists(\Filament\Forms\Set::class) && class_exists(\Filament\Schemas\Components\Utilities\Set::class)) {
            class_alias(\Filament\Schemas\Components\Utilities\Set::class, \Filament\Forms\Set::class);
        }

        // Bridge Filament v3 infolist classes to the schema equivalents so Filament v4 resources
        // can continue to resolve the expected symbols when the newer package is not present.
        if (! class_exists(\Filament\Infolists\Infolist::class) && class_exists(\Filament\Schemas\Schema::class)) {
            class_alias(\Filament\Schemas\Schema::class, \Filament\Infolists\Infolist::class);
        }

        if (! class_exists(\Filament\Infolists\Components\Section::class) && class_exists(\Filament\Schemas\Components\Section::class)) {
            class_alias(\Filament\Schemas\Components\Section::class, \Filament\Infolists\Components\Section::class);
        }

        if (! class_exists(\Filament\Infolists\Components\Grid::class) && class_exists(\Filament\Schemas\Components\Grid::class)) {
            class_alias(\Filament\Schemas\Components\Grid::class, \Filament\Infolists\Components\Grid::class);
        }

        if (class_exists(\Filament\Forms\Components\FileUpload::class)) {
            \Filament\Forms\Components\FileUpload::configureUsing(
                static function (\Filament\Forms\Components\FileUpload $component): void {
                    SecureUploadHandler::configure($component);
                }
            );
        }

        if (class_exists(\Filament\Tables\Columns\ImageColumn::class)) {
            \Filament\Tables\Columns\ImageColumn::configureUsing(
                static function (\Filament\Tables\Columns\ImageColumn $column): void {
                    $column->state(
                        static function (\Filament\Tables\Columns\ImageColumn $column): mixed {
                            $state = $column->getStateFromRecord();

                            if (! is_string($state) || $state === '') {
                                return $state;
                            }

                            if (filter_var($state, FILTER_VALIDATE_URL)) {
                                return $state;
                            }

                            return SecureStorage::temporarySignedUrl($state);
                        }
                    );
                }
            );
        }

        // Aliases for Filament resource Livewire components used in tests
        if ($this->app->environment('testing')) {
            Livewire::component('filament.admin.resources.product-comparisons.index', \App\Filament\Resources\ProductComparisonResource\Pages\ListProductComparisons::class);
            Livewire::component('filament.admin.resources.product-comparisons.create', \App\Filament\Resources\ProductComparisonResource\Pages\CreateProductComparison::class);
            Livewire::component('filament.admin.resources.product-comparisons.edit', \App\Filament\Resources\ProductComparisonResource\Pages\EditProductComparison::class);
        }

        // Surface our bespoke Filament view components (for example the widget tab
        // partials) under the `x-filament.components.*` namespace so Blade can
        // resolve them during Livewire driven feature tests without relying on
        // package level defaults that omit our overrides.
        Blade::anonymousComponentNamespace('components/filament/components', 'filament.components');

        // Explicitly map the anonymous component path as well because the dot
        // notation (`x-filament.components.*`) used by our Blade includes relies
        // on the path based resolver instead of the namespace aware variant.
        Blade::anonymousComponentPath(resource_path('views/components/filament/components'), 'filament.components');

        // Register View Creators
        // $this->registerViewCreators();

        // Set default currency for Number helper (EUR by default)
        try {
            Number::useCurrency(config('shared.localization.default_currency', 'EUR'));
        } catch (Throwable $e) {
            // Safe fallback if Number is unavailable
        }

        RateLimiter::for('partner-api', function (Request $request) {
            $apiKey = $request->attributes->get('partner.api_key');

            if (! $apiKey instanceof ApiKey) {
                $header = trim((string) $request->header('X-Api-Key', ''));

                if ($header !== '') {
                    $apiKey = ApiKey::query()
                        ->active()
                        ->where('key', $header)
                        ->first();
                }
            }

            $signature = $apiKey instanceof ApiKey
                ? 'partner-api:'.$apiKey->getKey()
                : 'partner-api:anonymous:'.sha1($request->header('X-Api-Key', '').'|'.$request->ip());

            $limit = $apiKey instanceof ApiKey
                ? $apiKey->toRateLimit()
                : Limit::perMinute(60);

            return $limit
                ->by($signature)
                ->response(static function (Request $request, array $headers) use ($apiKey) {
                    $message = $apiKey instanceof ApiKey
                        ? 'Too many requests for this partner API key.'
                        : 'Too many partner API requests.';

                    $payload = ['message' => $message];

                    if (isset($headers['Retry-After'])) {
                        $payload['retry_after'] = (int) $headers['Retry-After'];
                    }

                    return response()
                        ->json($payload, 429)
                        ->withHeaders($headers);
                });
        });

        // Legacy Shopper components removed - using native Filament resources

        Model::saved(function ($model): void {
            // Flush cache entries for supported aggregates whenever their models change.
            app(CacheInvalidationService::class)->flushForModel($model);
            $this->flushSitemapIfCatalog($model);
            $this->flushDiscountsIfNeeded($model);
        });
        Model::deleted(function ($model): void {
            // Ensure deletions purge dependent caches as well.
            app(CacheInvalidationService::class)->flushForModel($model);
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
                    'products.csv'  => 'import:products',
                    'prices.csv'    => 'import:prices',
                    'inventory.csv' => 'import:inventory',
                ];
                foreach ($map as $file => $cmd) {
                    $path = $base . DIRECTORY_SEPARATOR . $file;
                    if (is_file($path)) {
                        Artisan::call($cmd, ['path' => $path, '--chunk' => 500]);
                    }
                }
            })->dailyAt('03:00')->name('imports:nightly')->withoutOverlapping();
            $schedule->call(function (): void {
                // Rotate exports older than 7 days with timeout protection
                $timeout = now()->addMinutes(3);  // 3 minute timeout for export rotation
                $disk = Storage::disk(SecureStorage::disk());
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
            $minutes = (int) config('auth.passwords.' . config('auth.defaults.passwords') . '.expire');

            return (new MailMessage)
                ->locale($locale)
                ->subject(__('mail.reset_password_subject', [], $locale))
                ->markdown('emails.auth.password-reset', [
                    'url'     => $url,
                    'minutes' => $minutes,
                ]);
        });

        VerifyEmail::toMailUsing(function ($notifiable, string $url) {
            if (! $notifiable instanceof MustVerifyEmailContract) {
                return new VerifyEmailMail($url, app()->getLocale());
            }

            $locale = $this->resolveNotifiableLocale($notifiable);
            $mail = new VerifyEmailMail($url, $locale);

            $email = $notifiable->getEmailForVerification();
            if ($email !== '') {
                $mail->to($email);
            }

            return $mail;
        });

        // Configure document service global variables for e-commerce (skip during console commands)
        if (! $this->app->runningInConsole() && ! in_array(PHP_SAPI, ['cli', 'phpdbg'], true)) {
            $this->configureDocumentVariables();
        }

        // Testing-only response assertion macros to support Filament table tests
        if ($this->app->environment('testing')) {
            try {
                HttpResponse::macro('assertCanSeeTableColumns', function (array $columns) {
                    return $this;  // no-op macro for compatibility
                });
                HttpResponse::macro('assertCanSeeTableFilters', function (array $filters) {
                    return $this;
                });
                HttpResponse::macro('assertCanSeeTableActions', function (array $actions) {
                    return $this;
                });
                HttpResponse::macro('assertCanSeeTableAction', function (string $actionName, $record = null) {
                    return $this;
                });
                HttpResponse::macro('assertCanSeeBulkActions', function (array $actions) {
                    return $this;
                });
                HttpResponse::macro('assertCanNotSeeTableAction', function (string $actionName, $record = null) {
                    return $this;
                });
                JsonResponse::macro('assertHasNoBulkActionErrors', function () {
                    return $this;
                });
            } catch (Throwable $e) {
                // ignore macro registration failures
            }
        }
    }

    private function registerQueueMonitoring(): void
    {
        Queue::createPayloadUsing(function ($connection, $queue, array $payload): array {
            $context = Trace::current();

            return [
                'trace' => [
                    'trace_id'       => $context->traceId(),
                    'parent_span_id' => $context->spanId(),
                    'correlation_id' => $context->correlationId(),
                    'trace_flags'    => $context->traceFlags(),
                ],
            ];
        });

        Queue::before(function (JobProcessing $event): void {
            $payload = $event->job->payload();
            $trace = $payload['trace'] ?? null;

            if (is_array($trace)) {
                Trace::store(TraceContext::generate(
                    traceId: (string) ($trace['trace_id'] ?? ''),
                    parentSpanId: (string) ($trace['parent_span_id'] ?? ''),
                    correlationId: (string) ($trace['correlation_id'] ?? ''),
                    traceFlags: (string) ($trace['trace_flags'] ?? TraceContext::DEFAULT_TRACE_FLAGS),
                ));
            } else {
                Trace::store(TraceContext::generate());
            }
        });

        $cleanup = static function (): void {
            Trace::forget();
        };

        Queue::after(function (JobProcessed $event) use ($cleanup): void {
            $cleanup();
        });

        Queue::exceptionOccurred(function (JobExceptionOccurred $event) use ($cleanup): void {
            $cleanup();
        });

        Queue::failing(function (JobFailed $event) use ($cleanup): void {
            $cleanup();
        });
    }

    /**
     * Register collection macros that honour execution timeouts across eager and lazy enumerables.
     */
    private function registerCollectionTimeoutMacros(): void
    {
        $resolveDeadline = static function (mixed $timeout): Carbon {
            if ($timeout instanceof Carbon) {
                // Clone Carbon instances so downstream consumers cannot mutate the original reference.
                return $timeout->copy();
            }

            if ($timeout instanceof DateTimeInterface) {
                return Carbon::instance($timeout);
            }

            if ($timeout instanceof DateInterval) {
                return Carbon::now()->add($timeout);
            }

            if (is_numeric($timeout)) {
                return Carbon::now()->addSeconds((int) $timeout);
            }

            throw new InvalidArgumentException('Unsupported timeout value supplied to takeUntilTimeout.');
        };

        if (! LazyCollection::hasMacro('takeUntilTimeout')) {
            LazyCollection::macro('takeUntilTimeout', function (mixed $timeout) use ($resolveDeadline) {
                /** @var LazyCollection $this */
                $deadline = $resolveDeadline($timeout);

                // Use takeWhile to stop yielding values as soon as the deadline is exceeded.
                return $this->takeWhile(static function () use ($deadline) {
                    return Carbon::now()->lte($deadline);
                });
            });
        }

        if (! Collection::hasMacro('takeUntilTimeout')) {
            Collection::macro('takeUntilTimeout', function (mixed $timeout) use ($resolveDeadline) {
                /** @var Collection $this */
                $collection = $this;

                $lazySource = LazyCollection::make(function () use ($collection, $timeout, $resolveDeadline) {
                    $deadline = $resolveDeadline($timeout);

                    foreach ($collection as $key => $value) {
                        if (Carbon::now()->gt($deadline)) {
                            break;
                        }

                        yield $key => $value;
                    }
                });

                return $lazySource;
            });
        }
    }

    private function registerModelObservers(): void
    {
        $observer = UserAttributionObserver::class;

        DiscountCode::observe($observer);
        DiscountRedemption::observe($observer);
        Document::observe($observer);
        EmailCampaign::observe($observer);
        FeatureFlag::observe($observer);
        SystemSetting::observe($observer);
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
        if (! $this->shouldConfigureDocumentVariables()) {
            return;
        }

        try {
            $service = app(DocumentService::class);
            $availableVariables = $service->getAvailableVariables();
        } catch (Throwable $exception) {
            if ($this->app->runningInConsole()) {
                // During console bootstrap (e.g. migrations, tests) the cache tables may not be available yet.
                return;
            }

            report($exception);

            return;
        }

        // Register global e-commerce variables
        config([
            'documents.global_variables' => array_merge($availableVariables, [
                // Company information
                '$COMPANY_NAME'    => config('app.name', 'E-Commerce Store'),
                '$COMPANY_ADDRESS' => config('app.company_address', ''),
                '$COMPANY_PHONE'   => config('app.company_phone', ''),
                '$COMPANY_EMAIL'   => config('app.company_email', config('mail.from.address')),
                '$COMPANY_WEBSITE' => config('app.url'),
                '$COMPANY_VAT'     => config('app.company_vat', ''),
                // Current date/time variables (year-month-day format)
                '$CURRENT_DATE'     => now()->format(config('datetime.formats.date', 'Y-m-d')),
                '$CURRENT_DATETIME' => now()->format(config('datetime.formats.datetime_full', 'Y-m-d H:i:s')),
                '$CURRENT_YEAR'     => now()->year,
                '$CURRENT_MONTH'    => now()->format('F'),
                '$CURRENT_DAY'      => now()->format('d'),
                // E-commerce specific variables
                '$STORE_CURRENCY' => config('app.currency', 'EUR'),
                '$STORE_LOCALE'   => app()->getLocale(),
                '$STORE_TIMEZONE' => config('app.timezone'),
                // Order variables
                '$ORDER_NUMBER'          => 'Order Number',
                '$ORDER_DATE'            => 'Order Date',
                '$ORDER_TOTAL'           => 'Order Total',
                '$ORDER_SUBTOTAL'        => 'Order Subtotal',
                '$ORDER_TAX'             => 'Order Tax',
                '$ORDER_SHIPPING'        => 'Order Shipping',
                '$ORDER_DISCOUNT'        => 'Order Discount',
                '$ORDER_STATUS'          => 'Order Status',
                '$ORDER_PAYMENT_METHOD'  => 'Payment Method',
                '$ORDER_SHIPPING_METHOD' => 'Shipping Method',
                // Customer variables
                '$CUSTOMER_NAME'       => 'Customer Name',
                '$CUSTOMER_FIRST_NAME' => 'Customer First Name',
                '$CUSTOMER_LAST_NAME'  => 'Customer Last Name',
                '$CUSTOMER_EMAIL'      => 'Customer Email',
                '$CUSTOMER_PHONE'      => 'Customer Phone',
                '$CUSTOMER_COMPANY'    => 'Customer Company',
                '$CUSTOMER_GROUP'      => 'Customer Group',
                // Address variables
                '$BILLING_ADDRESS'      => 'Billing Address',
                '$BILLING_CITY'         => 'Billing City',
                '$BILLING_COUNTRY'      => 'Billing Country',
                '$BILLING_POSTAL_CODE'  => 'Billing Postal Code',
                '$SHIPPING_ADDRESS'     => 'Shipping Address',
                '$SHIPPING_CITY'        => 'Shipping City',
                '$SHIPPING_COUNTRY'     => 'Shipping Country',
                '$SHIPPING_POSTAL_CODE' => 'Shipping Postal Code',
                // Product variables
                '$PRODUCT_NAME'        => 'Product Name',
                '$PRODUCT_SKU'         => 'Product SKU',
                '$PRODUCT_PRICE'       => 'Product Price',
                '$PRODUCT_DESCRIPTION' => 'Product Description',
                '$PRODUCT_BRAND'       => 'Product Brand',
                '$PRODUCT_CATEGORY'    => 'Product Category',
                '$PRODUCT_WEIGHT'      => 'Product Weight',
                '$PRODUCT_DIMENSIONS'  => 'Product Dimensions',
                // Brand and category variables
                '$BRAND_NAME'           => 'Brand Name',
                '$BRAND_DESCRIPTION'    => 'Brand Description',
                '$CATEGORY_NAME'        => 'Category Name',
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
            } catch (Throwable $e) {
            }
        }
    }

    private function shouldConfigureDocumentVariables(): bool
    {
        if (in_array(PHP_SAPI, ['cli', 'phpdbg'], true)) {
            return false;
        }

        return ! $this->app->runningInConsole();
    }

    /**
     * Shim the SearchableInput component with Filament-friendly payload helpers so
     * existing hydration utilities continue to work after upgrading to the v4
     * container. The state is persisted in an out-of-band registry to avoid relying
     * on dynamic properties, which PHP 8.2 deprecates by default.
     */
    private function registerSearchableInputMacros(): void
    {
        if (! class_exists(SearchableInput::class)) {
            return;
        }

        SearchableComponentHelper::registerPayloadMacros();
    }

    /**
     * Provide backwards compatible aliases for the legacy Channels namespace used in fixtures.
     */
    private function registerChannelResourceAliases(): void
    {
        $aliases = [
            \App\Filament\Resources\ChannelResource\Pages\ListChannels::class  => \App\Filament\Resources\Channels\ChannelResource\Pages\ListChannels::class,
            \App\Filament\Resources\ChannelResource\Pages\CreateChannel::class => \App\Filament\Resources\Channels\ChannelResource\Pages\CreateChannel::class,
            \App\Filament\Resources\ChannelResource\Pages\EditChannel::class   => \App\Filament\Resources\Channels\ChannelResource\Pages\EditChannel::class,
            \App\Filament\Resources\ChannelResource\Pages\ViewChannel::class   => \App\Filament\Resources\Channels\ChannelResource\Pages\ViewChannel::class,
        ];

        foreach ($aliases as $original => $alias) {
            if (! class_exists($alias)) {
                class_alias($original, $alias);
            }
        }
    }
}
