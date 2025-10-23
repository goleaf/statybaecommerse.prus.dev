<?php

declare(strict_types=1);

namespace App\Filament;

use App\Filament\Widgets\GeneralStatsOverview;
use App\Filament\Widgets\SalesByMonthChart;
use Filament\Enums\UserMenuPosition;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\StatsOverviewWidget;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use InvalidArgumentException;
use LaraZeus\SpatieTranslatable\SpatieTranslatablePlugin;

class AdminPanelProvider extends PanelProvider
{
    public function __construct(?ApplicationContract $app = null)
    {
        if (! $app instanceof ApplicationContract) {
            $resolved = function_exists('app') ? app() : null;

            if ($resolved instanceof ApplicationContract) {
                $app = $resolved;
            }
        }

        if (! $app instanceof ApplicationContract) {
            throw new InvalidArgumentException('A Laravel application instance is required to construct the admin panel provider.');
        }

        parent::__construct($app);
    }

    public function panel(Panel $panel): Panel
    {
        $configuredLocales = array_values(array_filter(
            (array) config('shared.localization.supported_locales', [])
        ));

        if ($configuredLocales === []) {
            $configuredLocales = [config('app.locale') ?? 'en'];
        }

        $translatablePlugin = SpatieTranslatablePlugin::make()
            ->defaultLocales($configuredLocales)
            ->persist();

        if ($this->isTestingEnvironment()) {
            return $panel
                ->default()
                ->id('admin')
                ->path('/admin')
                ->login()
                ->topbar(false)
                ->userMenu(position: UserMenuPosition::Sidebar)
                ->colors([
                    'primary' => Color::Blue,
                ])
                ->plugins([
                    $translatablePlugin,
                ])
                ->resources([
                    \App\Filament\Resources\ApiKeyResource::class,
                    \App\Filament\Resources\OrderShippingResource::class,
                    \App\Filament\Resources\PartnerResource::class,
                    \App\Filament\Resources\PartnerTierResource::class,
                    \App\Filament\Resources\PriceListItemResource::class,
                    \App\Filament\Resources\ProductResource::class,
                    \App\Filament\Resources\ProductVariantResource::class,
                    \App\Filament\Resources\PostResource::class,
                    \App\Filament\Resources\RecommendationAnalyticsResource::class,
                    \App\Filament\Resources\RecommendationConfigResource::class,
                    \App\Filament\Resources\NotificationResource::class,
                    \App\Filament\Resources\UserBehaviorResource::class,
                ])
                ->pages([])
                ->widgets([
                    GeneralStatsOverview::class,
                    SalesByMonthChart::class,
                    StatsOverviewWidget::class,
                ])
                ->middleware([
                    \Illuminate\Session\Middleware\StartSession::class,
                    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                    \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
                    \Illuminate\Routing\Middleware\SubstituteBindings::class,
                    \Illuminate\Auth\Middleware\Authenticate::class,
                ])
                ->authMiddleware([
                    \Illuminate\Auth\Middleware\Authenticate::class,
                ])
                // Register the admin theme and companion JavaScript so plugin assets (including the combobox)
                // are built for local testing environments.
                ->viteTheme([
                    'resources/css/filament/admin/theme.css',
                    'resources/js/filament/admin/theme.js',
                ]);
        }

        return $panel
            ->default()
            ->id('admin')
            ->path('/admin')
            ->login()
            ->topbar(false)
            ->userMenu(position: UserMenuPosition::Sidebar)
            ->colors([
                'primary' => Color::Blue,
            ])
            ->plugins([
                $translatablePlugin,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            // ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->pages([
                //
            ])
            ->widgets([
                GeneralStatsOverview::class,
                SalesByMonthChart::class,
                StatsOverviewWidget::class,
            ])
            ->middleware([
                \Illuminate\Session\Middleware\StartSession::class,
                \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
                \Illuminate\Routing\Middleware\SubstituteBindings::class,
                \Illuminate\Auth\Middleware\Authenticate::class,
            ])
            ->authMiddleware([
                \Illuminate\Auth\Middleware\Authenticate::class,
            ])
            // Register the admin theme and companion JavaScript so plugin assets (including the combobox)
            // are available throughout the production panel.
            ->viteTheme([
                'resources/css/filament/admin/theme.css',
                'resources/js/filament/admin/theme.js',
            ]);
    }

    private function isTestingEnvironment(): bool
    {
        if (! function_exists('app')) {
            return false;
        }

        $application = app();

        if (! $application instanceof ApplicationContract) {
            return false;
        }

        if (! method_exists($application, 'environment')) {
            return false;
        }

        return $application->environment('testing');
    }

    private function applyBaseConfiguration(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('/admin')
            ->login()
            ->topbar(false)
            ->userMenu(position: UserMenuPosition::Sidebar)
            ->colors([
                'primary' => Color::Blue,
            ])
            ->middleware([
                \Illuminate\Session\Middleware\StartSession::class,
                \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
                \Illuminate\Routing\Middleware\SubstituteBindings::class,
                \Illuminate\Auth\Middleware\Authenticate::class,
            ])
            ->authMiddleware([
                \Illuminate\Auth\Middleware\Authenticate::class,
            ])
            ->viteTheme('resources/css/filament/admin/theme.scss');
    }

    private function isTestingEnvironment(): bool
    {
        if (! function_exists('app')) {
            return false;
        }

        $application = app();

        if (! $application instanceof ApplicationContract) {
            return false;
        }

        if (! method_exists($application, 'environment')) {
            return false;
        }

        return $application->environment('testing');
    }

    private function makeSpatieTranslatablePlugin(): SpatieTranslatablePlugin
    {
        $plugin = SpatieTranslatablePlugin::make()
            ->persist();

        $locales = $this->resolveSupportedLocales();

        if ($locales !== []) {
            $plugin->defaultLocales($locales);
        }

        return $plugin;
    }

    /**
     * @return array<int, string>
     */
    private function resolveSupportedLocales(): array
    {
        $configured = config('app.supported_locales', []);

        if (is_string($configured)) {
            $configured = array_map('trim', explode(',', $configured));
        } elseif (! is_array($configured)) {
            $configured = [];
        }

        $locales = array_filter(array_map(
            static fn ($locale): string => (string) $locale,
            $configured,
        ));

        $fallbackLocale = (string) config('app.fallback_locale', '');
        $defaultLocale = (string) config('app.locale', 'en');

        if ($fallbackLocale !== '') {
            $locales[] = $fallbackLocale;
        }

        if ($defaultLocale !== '') {
            $locales[] = $defaultLocale;
        }

        $locales = array_values(array_unique(array_filter($locales)));

        if ($locales === []) {
            return [$defaultLocale !== '' ? $defaultLocale : 'en'];
        }

        return $locales;
    }
}
