<?php

declare(strict_types=1);

namespace App\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\StatsOverviewWidget;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        if (app()->environment('testing')) {
            return $panel
                ->default()
                ->id('admin')
                ->path('/admin')
                ->login()
                ->colors([
                    'primary' => Color::Blue,
                ])
                ->resources([
                    \App\Filament\Resources\OrderShippingResource::class,
                    \App\Filament\Resources\PartnerResource::class,
                    \App\Filament\Resources\PartnerTierResource::class,
                    \App\Filament\Resources\PriceListItemResource::class,
                    \App\Filament\Resources\ProductResource::class,
                    \App\Filament\Resources\ProductVariantResource::class,
                    \App\Filament\Resources\PostResource::class,
                    \App\Filament\Resources\RecommendationAnalyticsResource::class,
                    \App\Filament\Resources\RecommendationConfigResource::class,
                ])
                ->pages([])
                ->widgets([
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
                ]);
        }

        return $panel
            ->default()
            ->id('admin')
            ->path('/admin')
            ->login()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            // ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->pages([
                //
            ])
            ->widgets([
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
            ]);
    }
}
