<?php

declare(strict_types=1);

namespace App\View\Creators;

use App\Services\Shared\CacheService;
use Illuminate\Contracts\View\View;

/**
 * GlobalDataCreator
 * 
 * View Creator that provides global data to all views immediately after instantiation.
 * This runs before View Composers, making it perfect for data that needs to be available
 * immediately when the view is created.
 */
final class GlobalDataCreator
{
    public function __construct(
        private readonly CacheService $cacheService
    ) {}

    /**
     * Create the view creator.
     */
    public function create(View $view): void
    {
        $view->with([
            // Global application data
            'appName' => config('app.name'),
            'appUrl' => config('app.url'),
            'appVersion' => config('app.version', '1.0.0'),
            'appEnvironment' => app()->environment(),
            
            // Current locale and currency
            'currentLocale' => app()->getLocale(),
            'currentCurrency' => current_currency(),
            'supportedLocales' => config('shared.localization.supported_locales', ['lt', 'en']),
            'supportedCurrencies' => config('shared.localization.supported_currencies', ['EUR']),
            
            // Global settings
            'isMaintenanceMode' => app()->isDownForMaintenance(),
            'isDebugMode' => config('app.debug', false),
            
            // Cache keys for performance
            'cachePrefix' => config('cache.prefix', 'laravel'),
            
            // Global timestamps (year-month-day format)
            'currentTimestamp' => now()->timestamp,
            'currentDate' => now()->format(config('datetime.formats.date', 'Y-m-d')),
            'currentDateTime' => now()->format(config('datetime.formats.datetime_full', 'Y-m-d H:i:s')),
        ]);
    }
}
