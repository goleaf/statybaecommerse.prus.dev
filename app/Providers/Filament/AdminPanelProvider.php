<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use Andreia\FilamentNordTheme\FilamentNordThemePlugin;
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
use Illuminate\Support\Facades\URL;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use LaraZeus\SpatieTranslatable\SpatieTranslatablePlugin;
use pxlrbt\FilamentExcel\FilamentExport;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;

final class AdminPanelProvider extends PanelProvider
{
    public function boot(): void
    {
        FilamentExport::createExportUrlUsing(
            static fn ($export): string => URL::temporarySignedRoute(
                'exports.signed-download',
                now()->addMinutes(60),
                ['export' => $export],
            ),
        );
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
            ->when(
                app()->environment('testing'),
                fn (Panel $p) => $p->plugins([$translatablePlugin]),
                fn (Panel $p) => $p->plugins(
                    array_values(array_filter([
                        $translatablePlugin,
                        FilamentShieldPlugin::make(),
                        class_exists(FilamentFullCalendarPlugin::class)
                            ? FilamentFullCalendarPlugin::make()
                                ->selectable(true)
                                ->editable(true)
                                ->timezone('Europe/Vilnius')
                                ->locale('lt')
                            : null,
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
                            ->gridLayoutButtonIcon('heroicon-o-squares-2x2'),
                        FilamentNordThemePlugin::make(),
                        ResizedColumnPlugin::make()->preserveOnDB(),
                    ]))
                ),
            )
            // Enable the custom Filament theme so third-party plugin views (like the searchable input)
            // are compiled with Tailwind during the build step.
            ->viteTheme('resources/css/filament/admin/theme.scss')
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
     * @return \Filament\Contracts\Plugin|null
     */
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
}
