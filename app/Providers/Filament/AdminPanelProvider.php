<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use Andreia\FilamentNordTheme\FilamentNordThemePlugin;
use App\Support\Nav;
use Asmit\ResizedColumn\ResizedColumnPlugin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;

use function class_exists;

use Filament\Contracts\Plugin as FilamentPlugin;
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
use Illuminate\Support\Facades\URL;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use LaraZeus\SpatieTranslatable\SpatieTranslatablePlugin;
use pxlrbt\FilamentExcel\FilamentExport;

final class AdminPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        if (class_exists(FilamentExport::class)) {
            FilamentExport::createExportUrlUsing(
                static fn ($export): string => URL::temporarySignedRoute(
                    'exports.signed-download',
                    now()->addMinutes(60),
                    ['export' => $export],
                ),
            );
        }
    }

    public function panel(Panel $panel): Panel
    {
        $configuredResources = array_values(array_filter(
            (array) config('filament.navigation.resources', []),
            static fn (mixed $resource): bool => is_string($resource),
        ));

        $resourceClasses = array_values(array_unique(
            [...Nav::orderedResources(), ...$configuredResources],
            SORT_STRING,
        ));

        /** @var array<class-string> $resourceClasses */
        $pageClasses = array_values(array_filter(
            (array) config('filament.navigation.pages', []),
            static fn (mixed $page): bool => is_string($page),
        ));

        /** @var array<class-string> $pageClasses */
        $supportedLocales = config('app.supported_locales', []);
        $defaultLocale = config('app.locale', 'en');

        $defaultLocales = collect(is_array($supportedLocales) ? $supportedLocales : explode(',', (string) $supportedLocales))
            ->map(static fn (mixed $locale): string => trim((string) $locale))
            ->filter()
            ->unique()
            ->values()
            ->whenEmpty(static fn ($locales) => $locales->push($defaultLocale))
            ->all();

        $translatablePlugin = SpatieTranslatablePlugin::make()
            ->defaultLocales($defaultLocales)
            ->persist();

        $plugins = [
            FilamentShieldPlugin::make(),
        ];

        if (class_exists(FilamentFullCalendarPlugin::class)) {
            $plugins[] = FilamentFullCalendarPlugin::make()
                ->selectable(true)
                ->editable(true)
                ->timezone('Europe/Vilnius')
                ->locale('lt');
        }

        $plugins[] = TableLayoutTogglePlugin::make()
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
            ->gridLayoutButtonIcon('heroicon-o-squares-2x2');

        $plugins[] = FilamentNordThemePlugin::make();
        $plugins[] = ResizedColumnPlugin::make()->preserveOnDB();

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile()
            ->when(
                app()->environment('testing'),
                fn (Panel $p) => $p->authGuard('web'),
                fn (Panel $p) => $p->authGuard('admin'),
            )
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
            ->resources($resourceClasses)
            ->pages($pageClasses)
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
            // Feed navigation groups generated from the shared helper so the sidebar uses
            // consistent icons, order, and collapse behaviour across the application.
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
            ->when(
                app()->environment('testing'),
                fn (Panel $p) => $p->plugins([]),
                fn (Panel $p) => $p->plugins($this->configuredPlugins()))
            // Enable the custom Filament theme and bundle supporting JavaScript so third-party plugin views
            // (like the searchable input and combobox) are compiled during the build step.
            ->viteTheme([
                'resources/css/filament/admin/theme.css',
                'resources/js/filament/admin/theme.js',
            ])
            ->spa();
    }

    /**
     * @return array<int, FilamentPlugin>
     */
    private function configuredPlugins(): array
    {
        $plugins = [];

        if (class_exists(FilamentShieldPlugin::class)) {
            $plugins[] = FilamentShieldPlugin::make();
        }

        if ($fullCalendar = $this->makeFullCalendarPlugin()) {
            $plugins[] = $fullCalendar;
        }

        if (class_exists(TableLayoutTogglePlugin::class)) {
            $tableLayoutPlugin = TableLayoutTogglePlugin::make()
                ->setDefaultLayout('grid')
                ->shareLayoutBetweenPages(false)
                ->displayToggleAction()
                ->toggleActionHook('tables::toolbar.search.after')
                ->listLayoutButtonIcon('heroicon-o-list-bullet')
                ->gridLayoutButtonIcon('heroicon-o-squares-2x2');

            if (class_exists(LocalStoragePersister::class)) {
                $tableLayoutPlugin->persistLayoutUsing(
                    persister: LocalStoragePersister::class,
                    cacheStore: 'redis',
                    cacheTtl: 60 * 24,
                );
            }

            $plugins[] = $tableLayoutPlugin;
        }

        if (class_exists(FilamentNordThemePlugin::class)) {
            $plugins[] = FilamentNordThemePlugin::make();
        }

        if (class_exists(SpatieTranslatablePlugin::class)) {
            $supportedLocales = array_values(array_filter(
                (array) config('shared.localization.supported_locales', []),
                static fn (mixed $locale): bool => is_string($locale) && $locale !== '',
            ));

            // Persist the admin locale switcher so users return to their last
            // editing language across Filament sessions.
            $plugins[] = SpatieTranslatablePlugin::make()
                ->defaultLocales($supportedLocales !== [] ? $supportedLocales : null)
                ->persist();
        }

        if (class_exists(ResizedColumnPlugin::class)) {
            $plugins[] = ResizedColumnPlugin::make()->preserveOnDB();
        }

        return array_values($plugins);
    }

    /**
     * Build Filament navigation groups by combining the central Nav registry with optional configuration overrides.
     *
     * @return array<int, NavigationGroup>
     */
    private function configuredNavigationGroups(): array
    {
        $configuredGroups = array_values(array_filter(
            (array) config('filament.navigation.groups', []),
            static fn (mixed $group): bool => is_array($group),
        ));

        /** @var array<string, array{label?: string, icon?: string|null, collapsed?: bool|null}> $overrides */
        $overrides = [];
        $extras = [];

        foreach ($configuredGroups as $group) {
            $key = $group['key'] ?? $group['label'] ?? null;

            if ($key !== null) {
                $overrides[$key] = $group;
            } else {
                $extras[] = $group;
            }
        }

        $navigationGroups = [];

        foreach (Nav::navigationGroups() as $group) {
            $override = $overrides[$group['key']] ?? null;
            $label = $override['label'] ?? $group['label'];
            $icon = $override['icon'] ?? $group['icon'];
            $collapsed = $override['collapsed'] ?? false;

            $navigationGroup = NavigationGroup::make()->label(__($label));

            if (! empty($icon)) {
                $navigationGroup->icon($icon);
            }

            if ($collapsed === true) {
                $navigationGroup->collapsed();
            }

            $navigationGroups[] = $navigationGroup;

            unset($overrides[$group['key']]);
        }

        foreach (array_merge(array_values($overrides), $extras) as $group) {
            $navigationGroup = NavigationGroup::make()
                ->label(__($group['label'] ?? ''));

            if (! empty($group['icon'])) {
                $navigationGroup->icon($group['icon']);
            }

            if (($group['collapsed'] ?? false) === true) {
                $navigationGroup->collapsed();
            }

            $navigationGroups[] = $navigationGroup;
        }

        return $navigationGroups;
    }

    private function makeFullCalendarPlugin(): ?\Filament\Contracts\Plugin
    {
        $pluginClass = 'Saade\\FilamentFullCalendar\\FilamentFullCalendarPlugin';

        if (! class_exists($pluginClass)) {
            return null;
        }

        return $pluginClass::make()
            ->selectable(true)
            ->editable(true)
            ->timezone('Europe/Vilnius')
            ->locale('lt');
    }

    /**
     * @template T of FilamentPlugin
     *
     * @param class-string<T> $pluginClass
     * @param (callable(T): T)|null $configure
     *
     * @return T|null
     */
    private function optionalPlugin(string $pluginClass, ?callable $configure = null): ?FilamentPlugin
    {
        if (! class_exists($pluginClass)) {
            return null;
        }

        /** @var FilamentPlugin $plugin */
        $plugin = $pluginClass::make();

        if ($configure !== null) {
            $plugin = $configure($plugin);
        }

        return $plugin;
    }
}
