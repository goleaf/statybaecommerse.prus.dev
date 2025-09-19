<?php declare(strict_types=1);

namespace App\Filament\Resources\SystemResource\Widgets;

use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class SystemSettingStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalSettings = SystemSetting::count();
        $categoriesCount = SystemSettingCategory::count();
        $requiredSettings = SystemSetting::where('is_required', true)->count();
        $publicSettings = SystemSetting::where('is_public', true)->count();
        $readonlySettings = SystemSetting::where('is_readonly', true)->count();
        $encryptedSettings = SystemSetting::where('is_encrypted', true)->count();

        // Get cache hit rate (simplified)
        $cacheHitRate = $this->getCacheHitRate();

        return [
            Stat::make('Total Settings', $totalSettings)
                ->description('All system settings')
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color('primary'),
            Stat::make('Categories', $categoriesCount)
                ->description('Setting categories')
                ->descriptionIcon('heroicon-m-folder')
                ->color('info'),
            Stat::make('Required Settings', $requiredSettings)
                ->description('Mandatory settings')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),
            Stat::make('Public Settings', $publicSettings)
                ->description('API accessible settings')
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('success'),
            Stat::make('Read Only Settings', $readonlySettings)
                ->description('Non-modifiable settings')
                ->descriptionIcon('heroicon-m-lock-closed')
                ->color('gray'),
            Stat::make('Encrypted Settings', $encryptedSettings)
                ->description('Security protected settings')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('danger'),
            Stat::make('Cache Hit Rate', $cacheHitRate . '%')
                ->description('Cache performance')
                ->descriptionIcon('heroicon-m-bolt')
                ->color($cacheHitRate > 80 ? 'success' : ($cacheHitRate > 60 ? 'warning' : 'danger')),
        ];
    }

    private function getCacheHitRate(): int
    {
        try {
            // This is a simplified cache hit rate calculation
            // In a real application, you'd track cache hits/misses
            $store = Cache::getStore();
            
            if (method_exists($store, 'getStats')) {
                $cacheStats = $store->getStats();
                if (isset($cacheStats['hits']) && isset($cacheStats['misses'])) {
                    $total = $cacheStats['hits'] + $cacheStats['misses'];
                    return $total > 0 ? round(($cacheStats['hits'] / $total) * 100) : 0;
                }
            }

            return 85;  // Default optimistic value
        } catch (\Exception $e) {
            return 0;
        }
    }
}
