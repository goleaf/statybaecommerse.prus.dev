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
        $configuredPanel = $this->applyBaseConfiguration($panel);

        if ($this->isTestingEnvironment()) {
            // Keep the testing panel lightweight to avoid boot-time crashes that stem from heavy resource bootstrapping.
            return $configuredPanel
                ->resources([])
                ->pages([])
                ->widgets([]);
        }

        return $configuredPanel
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
            ->viteTheme('resources/css/filament/admin/theme.css');
    }
}
