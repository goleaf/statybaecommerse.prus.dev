<?php declare(strict_types=1);

namespace App\Providers\Filament;

use Awcodes\Overlook\OverlookPlugin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Panel;
use Filament\PanelProvider;
use FilipFonal\FilamentLogManager\FilamentLogManagerPlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jacobtims\FilamentLogger\FilamentLoggerPlugin;
use Kenepa\ResourceLock\ResourceLockPlugin;

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
            ->brandName(__('admin.brand_name'))
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
            // ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->resources([
                // Products
                \App\Filament\Resources\ProductResource::class,
                \App\Filament\Resources\CategoryResource::class,
                \App\Filament\Resources\BrandResource::class,
                \App\Filament\Resources\CollectionResource::class,
                \App\Filament\Resources\ProductVariantResource::class,
                // Orders
                \App\Filament\Resources\OrderResource::class,
                \App\Filament\Resources\CartItemResource::class,
                // Users
                \App\Filament\Resources\UserResource::class,
                \App\Filament\Resources\CustomerGroupResource::class,
                \App\Filament\Resources\CustomerManagementResource::class,
                // Inventory
                \App\Filament\Resources\StockResource::class,
                // Locations
                \App\Filament\Resources\LocationResource::class,
                \App\Filament\Resources\CountryResource::class,
                \App\Filament\Resources\ZoneResource::class,
                // Marketing
                \App\Filament\Resources\CampaignResource::class,
                \App\Filament\Resources\DiscountCodeResource::class,
                \App\Filament\Resources\CouponResource::class,
                // Analytics
                \App\Filament\Resources\AnalyticsEventResource::class,
                // Reports
                \App\Filament\Resources\ReportResource::class,
                // Content
                \App\Filament\Resources\NewsResource::class,
                \App\Filament\Resources\PostResource::class,
                \App\Filament\Resources\LegalResource::class,
                // System
                \App\Filament\Resources\SystemSettingResource::class,
                \App\Filament\Resources\ActivityLogResource::class,
                // Sliders
                \App\Filament\Resources\Sliders\SliderResource::class,
            ])
            ->pages([
                \App\Filament\Pages\Dashboard::class,
                \App\Filament\Pages\SliderAnalytics::class,
            ])
            // ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\Filament\Clusters')
            // ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            // ->widgets([
            //     AccountWidget::class,
            // ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                // Set locale as early as possible so Filament builds UI in correct language
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
                    ->url(fn(): string => \App\Filament\Pages\Auth\EditProfile::getUrl())
                    ->icon('heroicon-o-user-circle'),
                'language' => \Filament\Navigation\MenuItem::make()
                    ->label(__('admin.navigation.language'))
                    ->url(fn(): string => route('language.switch', ['locale' => app()->getLocale() === 'lt' ? 'en' : 'lt']))
                    ->icon('heroicon-o-language'),
                // 'settings' => \Filament\Navigation\MenuItem::make()
                //     ->label(__('admin.navigation.settings'))
                //     ->url(fn(): string => \App\Filament\Resources\SystemSettingsResource::getUrl('index'))
                //     ->icon('heroicon-o-cog-6-tooth'),
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
                // FilamentLoggerPlugin::make(),
                // OverlookPlugin::make(),
                // ResourceLockPlugin::make(),
                // FilamentSocialitePlugin::make(),
                // FilamentLogManagerPlugin::make(),
            ])
            ->spa()
            // ->renderHook(
            //     'panels::topbar.end',
            //     fn (): string => view('filament.hooks.live-notification-feed-hook')->render()
            // )
            ->renderHook(
                'panels::topbar.start',
                fn(): string => view('filament.components.top-navigation')->render()
            )
            ->renderHook(
                'panels::body.end',
                fn(): string => view('filament.layouts.live-notifications-script')->render()
            );
    }
}
