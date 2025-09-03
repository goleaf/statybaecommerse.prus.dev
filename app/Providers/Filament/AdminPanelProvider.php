<?php declare(strict_types=1);

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
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
            ->brandName('E-Commerce Admin')
            ->brandLogo(asset('images/logo-admin.svg'))
            ->brandLogoHeight('2rem')
            ->favicon(asset('images/favicon.ico'))
            ->colors([
                'primary' => Color::Blue,
                'gray' => Color::Slate,
                'success' => Color::Green,
                'warning' => Color::Amber,
                'danger' => Color::Red,
                'info' => Color::Sky,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            // ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages') // Temporarily disabled
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
                \App\Filament\Widgets\EnhancedDashboardWidget::class,
                \App\Filament\Widgets\ComprehensiveStatsWidget::class,
                \App\Filament\Widgets\ModernSalesWidget::class,
                \App\Filament\Widgets\ProductManagementWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
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
            ->brandName('Statyba E-Commerce')
            ->globalSearch()
            ->globalSearchDebounce('500ms')
            ->breadcrumbs()
            ->unsavedChangesAlerts()
            ->databaseTransactions()
            ->readOnlyRelationManagersOnResourceViewPagesByDefault()
            ->navigationGroups([
                'Dashboard' => [
                    'label' => __('admin.navigation.dashboard'),
                    'icon' => 'heroicon-o-home',
                    'sort' => 1,
                ],
                'Catalog' => [
                    'label' => __('Catalog'),
                    'icon' => 'heroicon-o-cube',
                    'sort' => 2,
                ],
                'Orders' => [
                    'label' => __('admin.navigation.orders'),
                    'icon' => 'heroicon-o-shopping-bag',
                    'sort' => 3,
                ],
                'Customers' => [
                    'label' => __('admin.navigation.customers'),
                    'icon' => 'heroicon-o-users',
                    'sort' => 4,
                ],
                'Marketing' => [
                    'label' => __('Marketing'),
                    'icon' => 'heroicon-o-megaphone',
                    'sort' => 5,
                ],
                'Partners' => [
                    'label' => __('admin.navigation.partners'),
                    'icon' => 'heroicon-o-building-office',
                    'sort' => 6,
                ],
                'Content' => [
                    'label' => __('Content'),
                    'icon' => 'heroicon-o-document-text',
                    'sort' => 7,
                ],
                'Documents' => [
                    'label' => __('admin.navigation.documents'),
                    'icon' => 'heroicon-o-document-duplicate',
                    'sort' => 8,
                ],
                'Settings' => [
                    'label' => __('admin.navigation.settings'),
                    'icon' => 'heroicon-o-cog-6-tooth',
                    'sort' => 9,
                ],
                'System' => [
                    'label' => __('System'),
                    'icon' => 'heroicon-o-server',
                    'sort' => 10,
                ],
            ])
            ->userMenuItems([
                'profile' => \Filament\Navigation\MenuItem::make()
                    ->label(__('Profile'))
                    ->url(fn(): string => route('profile.edit'))
                    ->icon('heroicon-o-user-circle'),
                'settings' => \Filament\Navigation\MenuItem::make()
                    ->label(__('Settings'))
                    ->url(fn(): string => static::getUrl('settings'))
                    ->icon('heroicon-o-cog-6-tooth'),
            ])
            ->plugins([
                // Plugins temporarily disabled for upgrade
            ])
            ->spa();
    }
}