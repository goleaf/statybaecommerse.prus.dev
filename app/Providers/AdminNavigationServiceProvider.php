<?php declare(strict_types=1);

namespace App\Providers;

use App\Filament\Components\TopNavigation;
use Filament\Facades\Filament;
use Illuminate\Support\ServiceProvider;

class AdminNavigationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Top navigation is now handled in AdminPanelProvider
    }
}
