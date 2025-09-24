<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

final class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile()
            ->authGuard('web')
            ->authPasswordBroker('admin_users')
            ->brandName(__('admin.brand_name'))
            ->brandLogo(asset('images/logo-admin.svg'))
            ->brandLogoHeight('2rem')
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => Color::Blue,
                'gray' => Color::Slate,
                'success' => Color::Green,
                'warning' => Color::Amber,
                'danger' => Color::Red,
                'info' => Color::Sky,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
                \App\Filament\Pages\SliderAnalytics::class,
                \App\Filament\Pages\SliderManagement::class,
            ])
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
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->sidebarCollapsibleOnDesktop()
            ->topNavigation()
            ->maxContentWidth('full')
            ->font('Inter')
            ->darkMode()
            ->globalSearch()
            ->globalSearchDebounce('500ms')
            ->breadcrumbs()
            ->unsavedChangesAlerts()
            ->databaseTransactions()
            ->readOnlyRelationManagersOnResourceViewPagesByDefault()
            ->navigationGroups([
                NavigationGroup::make()->label(__('admin.navigation.dashboard'))->icon('heroicon-o-home'),
                NavigationGroup::make()->label(__('admin.navigation.commerce'))->icon('heroicon-o-shopping-bag'),
                NavigationGroup::make()->label(__('admin.navigation.products'))->icon('heroicon-o-cube'),
                NavigationGroup::make()->label(__('admin.navigation.marketing'))->icon('heroicon-o-megaphone'),
                NavigationGroup::make()->label(__('admin.navigation.content'))->icon('heroicon-o-document-text'),
                NavigationGroup::make()->label(__('admin.navigation.analytics'))->icon('heroicon-o-chart-bar'),
                NavigationGroup::make()->label(__('admin.navigation.system'))->icon('heroicon-o-cog-6-tooth'),
                NavigationGroup::make()->label('Recommendation System')->icon('heroicon-o-sparkles'),
            ])
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
                fn (Panel $p) => $p->plugins([
                    FilamentShieldPlugin::make(),
                ]))
            // Remove custom Vite theme to ensure default Filament styles load
            // ->viteTheme('resources/css/filament-enhancements.css')
            ->spa();
    }
}
