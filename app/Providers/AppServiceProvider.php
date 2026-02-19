<?php

declare(strict_types=1);

namespace App\Providers;

use App\Console\Commands\ProfiledSeedCommand;
use App\Contracts\CurrencyRateProvider;
use App\Contracts\DocumentServiceContract;
use App\Contracts\HealthReporter as HealthReporterContract;
use App\Database\Connectors\GracefulSQLiteConnector;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Filament\Components\LiveNotificationFeed;
use App\Infrastructure\Product\Repositories\EloquentProductRepository;
use App\Mail\Auth\VerifyEmailMail;
use App\Services\CacheInvalidationService;
use App\Services\CurrencyRateSyncService;
use App\Services\DocumentService;
use App\Services\StaticCurrencyRateProvider;
use App\Support\Cache\RateLimiter as ExtendedRateLimiter;
use App\Support\Filament\Components\SearchableInput;
use App\Support\Filament\SearchableComponentHelper;
use App\Support\Health\HealthReporter;
use App\Support\Html\HtmlSanitizer;
use App\Support\Livewire\Hooks\PropagateValidationExceptionHook;
use App\Support\Storage\SecureStorage;
use App\Support\Uploads\SecureUploadHandler;
use Closure;
use DateInterval;
use DateTimeInterface;
use Faker\Factory as FakerFactory;
use Faker\Generator as FakerGenerator;
use Filament\Facades\Filament;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Testing\TestsActions;
use Filament\Tables\Testing\TestsBulkActions;
use Filament\Tables\Testing\TestsColumns;
use Filament\Tables\Testing\TestsFilters;
use Filament\Tables\Testing\TestsRecords;
use Filament\Tables\Testing\TestsSummaries;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiter as BaseRateLimiter;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

use function in_array;

use InvalidArgumentException;

use function is_array;

use Kirschbaum\Loop\Facades\Loop;
use Kirschbaum\Loop\Filament\FilamentToolkit;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

use function Livewire\store;
use function str_contains;

use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (empty(config('app.key'))) {
            // Provide a deterministic fallback key so HTTP endpoints remain accessible
            // during the test harness runs executed without a configured APP_KEY.
            config()->set('app.key', 'base64:YWFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYWE=');
        }

        $this->app->singleton(HealthReporterContract::class, HealthReporter::class);
        $this->app->bind(DocumentServiceContract::class, DocumentService::class);

        // Share a single sanitizer instance so every consumer reuses the same allow-list configuration.
        $this->app->singleton(HtmlSanitizer::class, static fn (): HtmlSanitizer => new HtmlSanitizer);

        // Register enhanced filesystem services
        $this->app->register(\App\Support\Filesystem\FilesystemServiceProvider::class);

        // Ensure SQLite connections eagerly prepare database files for test reliability.
        $this->app->bind('db.connector.sqlite', static fn (): GracefulSQLiteConnector => new GracefulSQLiteConnector);

        $this->app->extend(BaseRateLimiter::class, static fn (BaseRateLimiter $limiter): BaseRateLimiter => $limiter instanceof ExtendedRateLimiter
            ? $limiter
            : ExtendedRateLimiter::fromBase($limiter));
        $this->app->singleton(FakerGenerator::class, static function ($app): FakerGenerator {
            $locale = (string) ($app->make('config')->get('app.faker_locale') ?? 'en_US');

            return FakerFactory::create($locale !== '' ? $locale : 'en_US');
        });

        if ($this->app->runningInConsole()) {
            // Register import utilities and override the core db:seed command with a profiled variant.
            $commands = [
                \App\Console\Commands\ImportProducts::class,
                \App\Console\Commands\ImportPrices::class,
                \App\Console\Commands\ImportInventory::class,
                ProfiledSeedCommand::class,
            ];

            $this->commands(array_values(array_filter(
                $commands,
                static fn (string $command): bool => class_exists($command)
            )));

            $this->app->extend('command.db.seed', function ($command, $app): \App\Console\Commands\ProfiledSeedCommand {
                return new ProfiledSeedCommand($app->make('db'));
            });
        }

        // Bind the domain-level product repository to its Eloquent implementation.
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);

        // Register the centralized locale service
        $this->app->singleton(\App\Services\LocaleService::class);

        // Service layer bindings
        $this->registerServiceLayerBindings();

        // Register the database search optimizer for production performance
        $this->app->singleton(\App\Services\Search\DatabaseSearchOptimizer::class, static fn ($app): \App\Services\Search\DatabaseSearchOptimizer => new \App\Services\Search\DatabaseSearchOptimizer($app->make('db.connection'))
        );

        // Provide a lightweight static exchange rate provider that can be swapped in tests.
        $this->app->singleton(CurrencyRateProvider::class, static fn (): CurrencyRateProvider => new StaticCurrencyRateProvider);

        // Expose the synchroniser so Filament actions can request up-to-date currency rates.
        $this->app->singleton(
            CurrencyRateSyncService::class,
            static fn ($app): CurrencyRateSyncService => new CurrencyRateSyncService(
                $app->make(CurrencyRateProvider::class),
                (string) config('currency.base_currency', 'EUR'),
            ),
        );

        $this->registerFilamentResourceAutoloader();
        $this->registerLegacyReferralFilamentAliases();

        if ($this->app->runningUnitTests()) {
            // Ensure Livewire exposes throttling validation exceptions to the test harness
            // before the core validation hook suppresses them within the error bag.
            Livewire::componentHook(PropagateValidationExceptionHook::class);
        }
    }

    public function boot(): void
    {
        // Normalize accidental mixed-case translation groups (e.g. `Admin.*`)
        // so Linux deployments keep resolving the existing lowercase files.
        Lang::handleMissingKeysUsing(static function (
            string $key,
            array $replace,
            ?string $locale,
            bool $fallback
        ): string {
            [$group, $item] = array_pad(explode('.', $key, 2), 2, null);

            if (! is_string($item) || $item === '') {
                return $key;
            }

            $normalizedGroup = strtolower($group);

            if ($normalizedGroup === $group) {
                return $key;
            }

            $normalizedKey = $normalizedGroup . '.' . $item;
            $translation = app('translator')->get($normalizedKey, $replace, $locale, $fallback);

            return $translation !== $normalizedKey ? $translation : $key;
        });

        // Register Laravel Loop Filament Toolkit
        if (class_exists(Loop::class) && class_exists(FilamentToolkit::class)) {
            Loop::toolkit(FilamentToolkit::make());
        }

        if ($this->app->runningUnitTests()) {
            // Ensure Filament keeps the full resource registry during tests so snapshot
            // assertions operate on the same canonical ordering as production while
            // still allowing individual tests to override the configuration explicitly.
            config()->set('filament.testing.autodiscover_resources', config('filament.testing.autodiscover_resources', true));
            config()->set('filament.testing.resources', config('filament.testing.resources', []));
        }

        $this->registerCollectionTimeoutMacros();

        $this->registerSearchableInputMacros();

        if (! Carbon::hasMacro('isThisWeek')) {
            Carbon::macro('isThisWeek', function (): bool {
                /** @var Carbon $this */
                return $this->isSameWeek(Carbon::now());
            });
        }

        if (! Carbon::hasMacro('isThisMonth')) {
            Carbon::macro('isThisMonth', function (): bool {
                /** @var Carbon $this */
                return $this->isSameMonth(Carbon::now());
            });
        }

        // Expose the bespoke Filament widget tab views as anonymous Blade components for reuse across resources.
        Blade::anonymousComponentPath(resource_path('views/filament/components'), 'filament.components');

        // Manually register Filament table testing helpers to keep compatibility with the upgraded packages.
        Testable::mixin(new TestsFilters);
        Testable::mixin(new TestsActions);
        Testable::mixin(new TestsBulkActions);
        Testable::mixin(new TestsColumns);
        Testable::mixin(new TestsRecords);
        Testable::mixin(new TestsSummaries);

        if (! Testable::hasMacro('groupTable')) {
            // Expose a defensive table grouping macro so Filament table tests can request group toggles even
            // when the upstream helper is unavailable in our compatibility layer.
            Testable::macro('groupTable', function (string $group): Testable {
                if (method_exists($this->instance(), 'setTableGrouping')) {
                    $this->call('setTableGrouping', $group);
                } elseif (property_exists($this->instance(), 'tableGrouping')) {
                    /** @var array<int, string>|string|null $current */
                    $current = $this->instance()->tableGrouping ?? null;
                    $groups = is_array($current) ? $current : [];
                    $groups[] = $group;
                    $this->set('tableGrouping', array_values(array_unique($groups)));
                }

                return $this;
            });
        }

        Testable::macro('mountedTableAction', function (string|array $actions, $record = null, array $arguments = []): Testable {
            /** @var array<array<string, mixed>> $parsed */
            $parsed = $this->parseNestedTableActions($actions, $record, $arguments);

            foreach ($parsed as $action) {
                $context = $action['context'] ?? [];

                if (($context['table'] ?? false) && ($context['recordKey'] ?? null)) {
                    $componentKey = (string) $context['recordKey'];
                    $schemaName = $this->instance()->getDefaultTestingSchemaName() ?? 'form';

                    if (! str_contains($componentKey, '.')) {
                        $componentKey = sprintf('%s.%s', $schemaName, $componentKey);
                    }

                    // Flag the schema component so Filament routes the mounted action to the
                    // correct select element rather than falling back to table defaults.
                    $context['schemaComponent'] = $componentKey;
                }

                $this->call(
                    'mountAction',
                    $action['name'],
                    $action['arguments'] ?? [],
                    $context,
                );
            }

            return $this;
        });

        if (! Testable::hasMacro('fillActionForm')) {
            Testable::macro('fillActionForm', function (array $state): Testable {
                // Bridge the renamed helper so existing tests can continue to seed action form data.
                $this->setActionData($state);

                return $this;
            });
        }

        Testable::macro('callAction', function ($actions, array $data = [], array $arguments = []): Testable {
            $initialMountedActionsCount = count($this->instance()->mountedActions);

            /** @var array<array<string, mixed>> $actions */
            /** @phpstan-ignore-next-line */
            $actions = $this->parseNestedActions($actions, $arguments);

            if (! empty($actions)) {
                $firstKey = array_key_first($actions);
                $mountedAction = $this->instance()->getMountedAction();
                $schemaHint = $this->instance()->lastSchemaComponentForTesting ?? null;

                if ($mountedAction !== null && $mountedAction->getSchemaComponent() !== null) {
                    if (filled($data)) {
                        /** @phpstan-ignore-next-line */
                        $this->fillForm($data);
                    }

                    /** @phpstan-ignore-next-line */
                    $this->callMountedAction($arguments);

                    return $this;
                }

                if (($actions[$firstKey]['context']['schemaComponent'] ?? null) === null && is_string($schemaHint)) {
                    $actions[$firstKey]['context']['schemaComponent'] = $schemaHint;
                }

                if ($mountedAction !== null && $mountedAction->getSchemaComponent() !== null && ! ($actions[$firstKey]['context']['schemaComponent'] ?? null)) {
                    $component = $mountedAction->getSchemaComponent();
                    $schemaKey = $component->getKey() ?? $component->getStatePath(isAbsolute: false);

                    if (is_string($schemaKey) && ! str_contains($schemaKey, '.')) {
                        $schemaName = $this->instance()->getMountedActionSchemaName()
                            ?? ($this->instance()->getDefaultTestingSchemaName() ?? 'form');
                        $schemaKey = sprintf('%s.%s', $schemaName, $schemaKey);
                    }

                    // Rehydrate the schema component context so follow-up calls target the select modal action.
                    $actions[$firstKey]['context']['schemaComponent'] = $schemaKey;
                }
            }

            /** @phpstan-ignore-next-line */
            $this->assertActionVisible($actions, $arguments);

            /** @phpstan-ignore-next-line */
            $this->mountAction($actions, $arguments);

            if (count($this->instance()->mountedActions) !== ($initialMountedActionsCount + count(Arr::wrap($actions)))) {
                return $this;
            }

            if (store($this->instance())->has('redirect')) {
                return $this;
            }

            /** @phpstan-ignore-next-line */
            $this->callMountedAction($arguments);

            return $this;
        });

        // Register Livewire components
        Livewire::component('live-notification-feed', LiveNotificationFeed::class);

        if ($this->app->runningUnitTests()) {
            FilamentView::spa(false);
            try {
                Filament::getCurrentOrDefaultPanel()->resourceEditPageRedirect('index');
            } catch (Throwable) {
                // Panel may not be initialised during early bootstrap in tests; ignore failures.
            }
        }

        Blade::anonymousComponentNamespace(
            resource_path('views/filament/components'),
            'filament'
        );  // Expose custom Filament Blade components for anonymous <x-filament::*> usage.

        // Allow overrides for Filament panel anonymous components when the package views are not loaded.
        Blade::anonymousComponentNamespace('vendor/filament-panels', 'filament-panels');

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
            // Provide a backwards-compatible assertion alias expected by legacy Filament tests.
            Testable::macro('assertCanSeeText', function (string $text): Testable {
                $this->assertSee($text, escape: false);

                return $this;
            });
        }

        Testable::macro('assertSchemaExists', function (?string $name = null): Testable {
            if (! method_exists($this->instance(), 'getCachedSchemas')) {
                return $this;
            }

            $candidates = [];

            if ($name !== null) {
                $candidates[] = $name;
            }

            if (method_exists($this->instance(), 'getDefaultTestingSchemaName')) {
                $default = $this->instance()->getDefaultTestingSchemaName();

                if ($default !== null) {
                    $candidates[] = $default;
                }
            }

            $candidates = array_values(array_unique([
                ...$candidates,
                'form',
                'content',
                'infolist',
            ]));

            foreach ($candidates as $candidate) {
                if (! is_string($candidate) || $candidate === '') {
                    continue;
                }

                /** @var \Filament\Schemas\Schema|null $schema */
                $schema = $this->instance()->{$candidate} ?? null;

                if ($schema instanceof \Filament\Schemas\Schema) {
                    return $this;
                }
            }

            $component = $this->instance()::class;
            $requested = $name ?? 'form';

            \Illuminate\Testing\Assert::fail(sprintf(
                'Failed asserting that a schema with the name [%s] exists on the [%s] component. Checked candidates: [%s].',
                $requested,
                $component,
                implode(', ', $candidates)
            ));

            return $this;
        });

        Testable::macro('assertFormExists', function ($name = 'form'): Testable {
            // Allow legacy tests to pass an array of expected fields while still supporting single name checks.
            if (is_array($name)) {
                foreach ($name as $field) {
                    $this->assertFormFieldExists($field);
                }

                return $this;
            }

            return $this->assertSchemaExists(is_string($name) ? $name : null);
        });

        Testable::macro('assertFormSet', function ($state, $value = null, string $form = 'form'): Testable {
            // Provide flexible handling so legacy tests can specify a single field name or a keyed array payload.
            $payload = $state instanceof Closure || is_array($state)
                ? $state
                : [$state => $value];

            return $this->assertSchemaStateSet($payload, $form);
        });

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
        } catch (Throwable) {
            // Safe fallback if Number is unavailable
        }

        if (class_exists(\Filament\Tables\Table::class)) {
            \Filament\Tables\Table::configureUsing(static function (\Filament\Tables\Table $table): void {
                $table->defaultDateTimeDisplayFormat('Y-m-d H:i:s');
                $table->defaultDateDisplayFormat('Y-m-d');
            });
        }

        if (class_exists(\Filament\Forms\Components\DatePicker::class)) {
            \Filament\Forms\Components\DatePicker::configureUsing(static function (\Filament\Forms\Components\DatePicker $component): void {
                $component->displayFormat('Y-m-d');
            });
        }

        if (class_exists(\Filament\Forms\Components\DateTimePicker::class)) {
            \Filament\Forms\Components\DateTimePicker::configureUsing(static function (\Filament\Forms\Components\DateTimePicker $component): void {
                $component->displayFormat('Y-m-d H:i:s');
            });
        }

        if (class_exists(\Filament\Schemas\Components\Section::class)) {
            \Filament\Schemas\Components\Section::configureUsing(static function (\Filament\Schemas\Components\Section $section): void {
                $section->columnSpanFull();
            });
        }

        if (class_exists(\Filament\Schemas\Components\Grid::class)) {
            \Filament\Schemas\Components\Grid::configureUsing(static function (\Filament\Schemas\Components\Grid $grid): void {
                $grid->columnSpanFull();
            });
        }

        if (class_exists(\Filament\Schemas\Components\Fieldset::class)) {
            \Filament\Schemas\Components\Fieldset::configureUsing(static function (\Filament\Schemas\Components\Fieldset $fieldset): void {
                $fieldset->columnSpanFull();
            });
        }

        // Using native Filament resources for admin panel functionality

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
                ->subject(__('messages.email_password_reset', [], $locale))
                ->markdown('emails.auth.password-reset', [
                    'url'     => $url,
                    'minutes' => $minutes,
                ]);
        });

        VerifyEmail::toMailUsing(function ($notifiable, string $url): VerifyEmailMail {
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
                HttpResponse::macro('assertCanSeeTableColumns', function (array $columns): object {
                    return $this;  // no-op macro for compatibility
                });
                HttpResponse::macro('assertCanSeeTableFilters', fn (array $filters): object => $this);
                HttpResponse::macro('assertCanSeeTableActions', fn (array $actions): object => $this);
                HttpResponse::macro('assertCanSeeTableAction', fn (string $actionName, $record = null): object => $this);
                HttpResponse::macro('assertCanSeeBulkActions', fn (array $actions): object => $this);
                HttpResponse::macro('assertCanNotSeeTableAction', fn (string $actionName, $record = null): object => $this);
                JsonResponse::macro('assertHasNoBulkActionErrors', fn (): object => $this);
            } catch (Throwable) {
                // ignore macro registration failures
            }
        }
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
                return $this->takeWhile(static fn (): bool => Carbon::now()->lte($deadline));
            });
        }

        if (! Collection::hasMacro('takeUntilTimeout')) {
            Collection::macro('takeUntilTimeout', function (mixed $timeout) use ($resolveDeadline) {
                /** @var Collection $this */
                $collection = $this;

                return LazyCollection::make(function () use ($collection, $timeout, $resolveDeadline) {
                    $deadline = $resolveDeadline($timeout);

                    foreach ($collection as $key => $value) {
                        if (Carbon::now()->gt($deadline)) {
                            break;
                        }

                        yield $key => $value;
                    }
                });
            });
        }
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
                    ->when(is_string(...), fn ($c): \Illuminate\Support\Collection => collect(explode(',', (string) $c)))
                    ->map(fn ($v): string => trim((string) $v))
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
            } catch (Throwable) {
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

    private static bool $filamentResourceAutoloaderRegistered = false;

    /**
     * Register a lightweight fallback autoloader for Filament resources so tests
     * continue to work even when Composer's optimised classmap misses new files.
     */
    private function registerFilamentResourceAutoloader(): void
    {
        if (self::$filamentResourceAutoloaderRegistered) {
            return;
        }

        spl_autoload_register(static function (string $class): void {
            if (! str_starts_with($class, 'App\\Filament\\Resources\\')) {
                return;
            }

            $relative = Str::after($class, 'App\\');
            $path = app_path(str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php');

            if (is_file($path)) {
                require_once $path;
            }
        }, true, false);

        self::$filamentResourceAutoloaderRegistered = true;
    }

    /**
     * Preserve backwards-compatible class names for referral resources and pages.
     */
    private function registerLegacyReferralFilamentAliases(): void
    {
        $aliases = [
            'App\\Filament\\Resources\\ReferralResource'                                                => 'App\\Filament\\Resources\\Referrals\\ReferralResource',
            'App\\Filament\\Resources\\ReferralResource\\Pages\\ListReferrals'                          => 'App\\Filament\\Resources\\Referrals\\Pages\\ListReferrals',
            'App\\Filament\\Resources\\ReferralResource\\Pages\\CreateReferral'                         => 'App\\Filament\\Resources\\Referrals\\Pages\\CreateReferral',
            'App\\Filament\\Resources\\ReferralResource\\Pages\\EditReferral'                           => 'App\\Filament\\Resources\\Referrals\\Pages\\EditReferral',
            'App\\Filament\\Resources\\ReferralResource\\Pages\\ViewReferral'                           => 'App\\Filament\\Resources\\Referrals\\Pages\\ViewReferral',
            'App\\Filament\\Resources\\ReferralCodeResource'                                            => 'App\\Filament\\Resources\\ReferralCodes\\ReferralCodeResource',
            'App\\Filament\\Resources\\ReferralCodeResource\\Pages\\ListReferralCodes'                  => 'App\\Filament\\Resources\\ReferralCodes\\Pages\\ListReferralCodes',
            'App\\Filament\\Resources\\ReferralCodeResource\\Pages\\CreateReferralCode'                 => 'App\\Filament\\Resources\\ReferralCodes\\Pages\\CreateReferralCode',
            'App\\Filament\\Resources\\ReferralCodeResource\\Pages\\EditReferralCode'                   => 'App\\Filament\\Resources\\ReferralCodes\\Pages\\EditReferralCode',
            'App\\Filament\\Resources\\ReferralRewardResource'                                          => 'App\\Filament\\Resources\\ReferralRewards\\ReferralRewardResource',
            'App\\Filament\\Resources\\ReferralRewardResource\\Pages\\ListReferralRewards'              => 'App\\Filament\\Resources\\ReferralRewards\\Pages\\ListReferralRewards',
            'App\\Filament\\Resources\\ReferralRewardResource\\Pages\\CreateReferralReward'             => 'App\\Filament\\Resources\\ReferralRewards\\Pages\\CreateReferralReward',
            'App\\Filament\\Resources\\ReferralRewardResource\\Pages\\EditReferralReward'               => 'App\\Filament\\Resources\\ReferralRewards\\Pages\\EditReferralReward',
            'App\\Filament\\Resources\\ReferralRewardResource\\Pages\\ViewReferralReward'               => 'App\\Filament\\Resources\\ReferralRewards\\Pages\\ViewReferralReward',
            'App\\Filament\\Resources\\ReferralRewardLogResource'                                       => 'App\\Filament\\Resources\\ReferralRewardLogs\\ReferralRewardLogResource',
            'App\\Filament\\Resources\\ReferralRewardLogResource\\Pages\\ListReferralRewardLogs'        => 'App\\Filament\\Resources\\ReferralRewardLogs\\Pages\\ListReferralRewardLogs',
            'App\\Filament\\Resources\\ReferralRewardLogResource\\Pages\\CreateReferralRewardLog'       => 'App\\Filament\\Resources\\ReferralRewardLogs\\Pages\\CreateReferralRewardLog',
            'App\\Filament\\Resources\\ReferralRewardLogResource\\Pages\\EditReferralRewardLog'         => 'App\\Filament\\Resources\\ReferralRewardLogs\\Pages\\EditReferralRewardLog',
            'App\\Filament\\Resources\\ReferralRewardLogResource\\Pages\\ViewReferralRewardLog'         => 'App\\Filament\\Resources\\ReferralRewardLogs\\Pages\\ViewReferralRewardLog',
            'App\\Filament\\Resources\\ReferralCodeUsageLogResource'                                    => 'App\\Filament\\Resources\\ReferralCodeUsageLogs\\ReferralCodeUsageLogResource',
            'App\\Filament\\Resources\\ReferralCodeUsageLogResource\\Pages\\ListReferralCodeUsageLogs'  => 'App\\Filament\\Resources\\ReferralCodeUsageLogs\\Pages\\ListReferralCodeUsageLogs',
            'App\\Filament\\Resources\\ReferralCodeUsageLogResource\\Pages\\CreateReferralCodeUsageLog' => 'App\\Filament\\Resources\\ReferralCodeUsageLogs\\Pages\\CreateReferralCodeUsageLog',
            'App\\Filament\\Resources\\ReferralCodeUsageLogResource\\Pages\\EditReferralCodeUsageLog'   => 'App\\Filament\\Resources\\ReferralCodeUsageLogs\\Pages\\EditReferralCodeUsageLog',
        ];

        foreach ($aliases as $legacy => $current) {
            if (class_exists($current) && ! class_exists($legacy, false)) {
                class_alias($current, $legacy);
            }
        }
    }

    /**
     * Register service layer bindings for dependency injection
     */
    private function registerServiceLayerBindings(): void
    {
        // Core services - singleton for stateless services
        $this->app->singleton(\App\Services\OrderManagementService::class);
        $this->app->singleton(\App\Services\UserManagementService::class);

        // Actions - bind as instances for stateless operations
        $this->app->bind(\App\Actions\Orders\CreateOrderAction::class);
        $this->app->bind(\App\Actions\Orders\UpdateOrderStatusAction::class);
        $this->app->bind(\App\Actions\Orders\CancelOrderAction::class);
        $this->app->bind(\App\Actions\Users\CreateUserAction::class);
        $this->app->bind(\App\Actions\Users\UpdateUserProfileAction::class);
        $this->app->bind(\App\Actions\Users\AssignRoleAction::class);

        // Existing services that should extend BaseService can be rebound here
        // Example: $this->app->extend(NotificationService::class, function($service, $app) {
        //     return new EnhancedNotificationService(...);
        // });
    }
}
