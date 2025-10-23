<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use Asmit\ResizedColumn\ResizedColumnPlugin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Enums\UserMenuPosition;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\SpatieLaravelMediaLibraryPlugin\FilamentSpatieLaravelMediaLibraryPlugin;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Hydrat\TableLayoutToggle\Persisters\LocalStoragePersister;
use Hydrat\TableLayoutToggle\TableLayoutTogglePlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Collection;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use LaraZeus\SpatieTranslatable\SpatieTranslatablePlugin;

final class AdminPanelProvider extends PanelProvider
{
    /** @var array<string, mixed>|null */
    private static ?array $filamentConfigCache = null;

    public function __construct(?Application $app = null)
    {
        if ($app instanceof Application) {
            parent::__construct($app);

            return;
        }

        $container = Container::getInstance();

        if ($container instanceof Application) {
            parent::__construct($container);

            return;
        }

        $this->app = null;
    }

    public function panel(Panel $panel): Panel
    {
        $resourceClasses = array_values(array_filter(
            (array) config('filament.navigation.resources', []),
            static fn (mixed $resource): bool => is_string($resource),
        ));

        /** @var array<class-string> $resourceClasses */
        $pageClasses = array_values(array_filter(
            (array) config('filament.navigation.pages', []),
            static fn (mixed $page): bool => is_string($page),
        ));

        /** @var array<class-string> $pageClasses */
        $configuredLocales = config('app.supported_locales', ['lt', 'en']);
        $defaultLocales = collect(is_array($configuredLocales) ? $configuredLocales : explode(',', (string) $configuredLocales))
            ->map(static fn (mixed $locale): string => trim((string) $locale))
            ->filter()
            ->values()
            ->all();

        if ($defaultLocales === []) {
            $defaultLocales = ['en'];
        }

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile()
            ->topbar(false)
            ->when($isTesting,
                fn (Panel $p) => $p->authGuard('web'),
                fn (Panel $p) => $p->authGuard('admin'))
            ->authPasswordBroker('admin_users')
            ->brandName(__('admin.brand_name'))
            ->brandLogo(fn (): string => asset('images/logo-admin.svg'))
            ->brandLogoHeight('2rem')
            ->favicon(fn (): string => asset('favicon.ico'))
            ->colors([
                'primary' => Color::Blue,
                'gray'    => Color::Slate,
                'success' => Color::Green,
                'warning' => Color::Amber,
                'danger'  => Color::Red,
                'info'    => Color::Sky,
            ])
            ->discoverResources(in: $this->appPath('Filament/Resources'), for: 'App\Filament\Resources')
            ->resources($resourceClasses)
            ->when(
                app()->environment('testing'),
                fn (Panel $p) => $p->pages([]),
                fn (Panel $p) => $p->pages($pageClasses)
            )
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                \App\Http\Middleware\SetFilamentLocale::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            // Disable database notifications polling to prevent auto-refresh on the main page
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->font('Inter')
            ->darkMode()
            ->globalSearch()
            ->globalSearchDebounce('500ms')
            ->breadcrumbs()
            ->unsavedChangesAlerts()
            ->databaseTransactions()
            ->readOnlyRelationManagersOnResourceViewPagesByDefault()
            ->navigationGroups($this->configuredNavigationGroups())
            ->userMenu(position: UserMenuPosition::Sidebar)
            ->userMenuItems([
                'profile' => \Filament\Navigation\MenuItem::make()
                    ->label($this->translate('admin.navigation.profile'))
                    ->url(fn (): string => \App\Filament\Pages\Auth\EditProfile::getUrl())
                    ->icon('heroicon-o-user-circle'),
                'language' => \Filament\Navigation\MenuItem::make()
                    ->label($this->translate('admin.navigation.language'))
                    ->url(fn (): string => $this->routeUrl('language.switch', [
                        'locale' => $this->currentLocale() === 'lt' ? 'en' : 'lt',
                    ]))
                    ->icon('heroicon-o-language'),
            ])
            ->plugin(
                SpatieTranslatablePlugin::make()
                    ->defaultLocales($defaultLocales)
                    ->persist()
            )
            ->when(app()->environment('testing'),
                static fn (Panel $p) => $p,
                static fn (Panel $p) => $p
                    ->plugin(FilamentShieldPlugin::make())
                    ->plugin(
                        TableLayoutTogglePlugin::make()
                            ->setDefaultLayout('grid')
                            ->persistLayoutUsing(
                                persister: LocalStoragePersister::class,
                                cacheStore: 'redis',
                                cacheTtl: 60 * 24,
                            )
                            ->shareLayoutBetweenPages(false)
                            ->displayToggleAction()
                            ->toggleActionHook('tables::toolbar.search.after')
                            ->listLayoutButtonIcon('heroicon-o-list-bullet')
                            ->gridLayoutButtonIcon('heroicon-o-squares-2x2')
                    )
            )
            // Enable the custom Filament theme so third-party plugin views (like the searchable input)
            // are compiled with Tailwind during the build step.
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->spa();
    }

    /**
     * Build Filament navigation groups from configuration.
     *
     * @return array<int, NavigationGroup>
     */
    private function configuredNavigationGroups(): array
    {
        $groupConfigurations = array_values(array_filter(
            (array) $this->configValue('filament.navigation.groups', []),
            static fn (mixed $group): bool => is_array($group),
        ));

        /** @var array<int, array{label?: string, icon?: string|null, collapsed?: bool|null}> $groupConfigurations */

        return Collection::make($groupConfigurations)
            ->map(function (array $group): NavigationGroup {
                $navigationGroup = NavigationGroup::make()
                    ->label($this->translate($group['label'] ?? ''));

                if (! empty($group['icon'])) {
                    $navigationGroup->icon($group['icon']);
                }

                if (($group['collapsed'] ?? false) === true) {
                    $navigationGroup->collapsed();
                }

                return $navigationGroup;
            })
            ->all();
    }

    /**
     * Normalise configured class lists into unique string arrays.
     *
     * @return array<int, class-string>
     */
    private function stringClassList(string $key): array
    {
        $items = $this->configValue($key, []);

        if (! is_array($items)) {
            return [];
        }

        $filtered = array_filter($items, static fn ($item): bool => is_string($item) && $item !== '' && class_exists($item));

        /** @var array<int, class-string> $normalized */
        $normalized = array_values(array_unique($filtered));

        return $normalized;
    }

    private function isTestingEnvironment(): bool
    {
        $application = $this->application();

        if (! $application) {
            return false;
        }

        try {
            return $application->environment('testing');
        } catch (Throwable) {
            return false;
        }
    }

    private function assetUrl(string $path): string
    {
        if (function_exists('asset')) {
            try {
                return asset($path);
            } catch (Throwable) {
                // ignore and fall back
            }
        }

        return '/'.ltrim($path, '/');
    }

    private function appPath(string $path): string
    {
        if (function_exists('app_path')) {
            try {
                return app_path($path);
            } catch (Throwable) {
                // ignore and fall back
            }
        }

        return rtrim(dirname(__DIR__, 2).'/'.$path, '/');
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    private function routeUrl(string $name, array $parameters = [], string $default = '#'): string
    {
        if (function_exists('route')) {
            try {
                return route($name, $parameters);
            } catch (Throwable) {
                return $default;
            }
        }

        return $default;
    }

    private function currentLocale(): string
    {
        $application = $this->application();

        if ($application) {
            try {
                return (string) $application->getLocale();
            } catch (Throwable) {
                // ignore and fall back
            }
        }

        $translator = $this->translator();

        if ($translator) {
            try {
                return (string) $translator->getLocale();
            } catch (Throwable) {
                // ignore and fall back
            }
        }

        return 'en';
    }

    private function translate(?string $key, string $default = ''): string
    {
        if ($key === null || $key === '') {
            return $default;
        }

        $translator = $this->translator();

        if (! $translator) {
            return $key;
        }

        try {
            $translated = $translator->get($key);

            return is_string($translated) ? $translated : $key;
        } catch (Throwable) {
            return $key;
        }
    }

    private function configValue(string $key, mixed $default = null): mixed
    {
        $repository = $this->configRepository();

        if ($repository) {
            return $repository->get($key, $default);
        }

        if (! str_starts_with($key, 'filament.')) {
            return $default;
        }

        $relativeKey = substr($key, strlen('filament.'));
        $config = $this->filamentConfig();

        foreach (explode('.', $relativeKey) as $segment) {
            if (! is_array($config) || ! array_key_exists($segment, $config)) {
                return $default;
            }

            $config = $config[$segment];
        }

        return $config;
    }

    private function configRepository(): ?ConfigRepository
    {
        $application = $this->application();

        if (! $application || ! $application->bound('config')) {
            return null;
        }

        try {
            $repository = $application->make('config');
        } catch (Throwable) {
            return null;
        }

        if (! $repository instanceof ConfigRepository) {
            return null;
        }

        return $repository;
    }

    private function filamentConfig(): array
    {
        if (self::$filamentConfigCache !== null) {
            return self::$filamentConfigCache;
        }

        $path = dirname(__DIR__, 3).'/config/filament.php';

        if (is_file($path)) {
            $config = require $path;

            if (is_array($config)) {
                /** @var array<string, mixed> $config */
                return self::$filamentConfigCache = $config;
            }
        }

        return self::$filamentConfigCache = [];
    }

    private function application(): ?Application
    {
        $container = Container::getInstance();

        return $container instanceof Application ? $container : null;
    }

    private function translator(): ?Translator
    {
        $application = $this->application();

        if (! $application || ! $application->bound('translator')) {
            return null;
        }

        try {
            $translator = $application->make('translator');
        } catch (Throwable) {
            return null;
        }

        if (! $translator instanceof Translator) {
            return null;
        }

        return $translator;
    }
}
