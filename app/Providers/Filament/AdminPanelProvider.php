<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use Andreia\FilamentNordTheme\FilamentNordThemePlugin;
use Asmit\ResizedColumn\ResizedColumnPlugin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;

use function class_exists;

use Filament\Enums\UserMenuPosition;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
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
use pxlrbt\FilamentExcel\FilamentExport;

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

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile()
            ->when(app()->environment('testing'),
                fn (Panel $p) => $p->authGuard('web'),
                fn (Panel $p) => $p->authGuard('admin'))
            ->authPasswordBroker('admin_users')
            ->brandName(__('admin.brand_name'))
            ->brandLogo(asset('images/logo-admin.svg'))
            ->brandLogoHeight('2rem')
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => Color::Blue,
                'gray'    => Color::Slate,
                'success' => Color::Green,
                'warning' => Color::Amber,
                'danger'  => Color::Red,
                'info'    => Color::Sky,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
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
            ->navigationGroups($this->configuredNavigationGroups())
            ->userMenu(position: UserMenuPosition::Sidebar)
            ->userMenuItems([
                'profile' => \Filament\Navigation\MenuItem::make()
                    ->label(__('admin.navigation.profile'))
                    ->url(fn (): string => \App\Filament\Pages\Auth\EditProfile::getUrl())
                    ->icon('heroicon-o-user-circle'),
                'language' => \Filament\Navigation\MenuItem::make()
                    ->label(__('admin.navigation.language'))
                    ->url(fn (): string => route('language.switch', ['locale' => app()->getLocale() === 'lt' ? 'en' : 'lt']))
                    ->icon('heroicon-o-language'),
            ])
            ->when(app()->environment('testing'),
                fn (Panel $p) => $p->plugins([]),
                fn (Panel $p) => $p->plugins(array_values(array_filter([
                    $this->makeFilamentShieldPlugin(),
                    $this->makeFullCalendarPlugin(),
                    $this->makeTableLayoutTogglePlugin(),
                    $this->makeFilamentNordThemePlugin(),
                    $this->makeResizedColumnPlugin(),
                ]))))
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
            (array) config('filament.navigation.groups', []),
            static fn (mixed $group): bool => is_array($group),
        ));

        /** @var array<int, array{label?: string, icon?: string|null, collapsed?: bool|null}> $groupConfigurations */

        return collect($groupConfigurations)
            ->map(static function (array $group, int|string $unused): NavigationGroup {
                $navigationGroup = NavigationGroup::make()
                    ->label(__($group['label'] ?? ''));

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

    private function makeFilamentShieldPlugin(): ?\Filament\Contracts\Plugin
    {
        if (! class_exists(FilamentShieldPlugin::class)) {
            return null;
        }

        return FilamentShieldPlugin::make();
    }

    private function makeFilamentNordThemePlugin(): ?\Filament\Contracts\Plugin
    {
        if (! class_exists(FilamentNordThemePlugin::class)) {
            return null;
        }

        return FilamentNordThemePlugin::make();
    }

    private function makeResizedColumnPlugin(): ?\Filament\Contracts\Plugin
    {
        if (! class_exists(ResizedColumnPlugin::class)) {
            return null;
        }

        return ResizedColumnPlugin::make()->preserveOnDB();
    }

    private function makeTableLayoutTogglePlugin(): ?\Filament\Contracts\Plugin
    {
        if (! class_exists(TableLayoutTogglePlugin::class)) {
            return null;
        }

        $plugin = TableLayoutTogglePlugin::make()
            ->setDefaultLayout('grid')
            ->shareLayoutBetweenPages(false)
            ->displayToggleAction()
            ->toggleActionHook('tables::toolbar.search.after')
            ->listLayoutButtonIcon('heroicon-o-list-bullet')
            ->gridLayoutButtonIcon('heroicon-o-squares-2x2');

        if (class_exists(LocalStoragePersister::class)) {
            $plugin->persistLayoutUsing(
                persister: LocalStoragePersister::class,
                cacheStore: 'redis',
                cacheTtl: 60 * 24,
            );
        }

        return $plugin;
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
}
