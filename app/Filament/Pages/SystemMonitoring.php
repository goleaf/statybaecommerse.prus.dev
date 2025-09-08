<?php declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use BackedEnum;
use UnitEnum;

final class SystemMonitoring extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cpu-chip';

    protected static string|UnitEnum|null $navigationGroup = 'System';
    protected static ?int $navigationSort = 1;

    public array $systemStats = [];
    public array $databaseStats = [];
    public array $queueStats = [];

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.system_monitoring');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('Admin');
    }

    public function mount(): void
    {
        $this->loadSystemStats();
    }

    public function loadSystemStats(): void
    {
        // System statistics
        $this->systemStats = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'memory_usage' => $this->formatBytes(memory_get_usage(true)),
            'memory_peak' => $this->formatBytes(memory_get_peak_usage(true)),
            'uptime' => $this->getSystemUptime(),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'mail_driver' => config('mail.default'),
        ];

        // Database statistics
        try {
            $this->databaseStats = [
                'connection' => config('database.default'),
                'total_tables' => collect(DB::select('SELECT name FROM sqlite_master WHERE type="table"'))->count(),
                'total_products' => Product::count(),
                'total_orders' => Order::count(),
                'total_users' => User::count(),
                'database_size' => $this->getDatabaseSize(),
            ];
        } catch (\Exception $e) {
            $this->databaseStats = ['error' => $e->getMessage()];
        }

        // Queue statistics
        $this->queueStats = [
            'pending_jobs' => DB::table('jobs')->count(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label(__('admin.actions.refresh'))
                ->icon('heroicon-o-arrow-path')
                ->action(fn() => $this->loadSystemStats()),

            Action::make('clear_cache')
                ->label(__('admin.actions.clear_cache'))
                ->icon('heroicon-o-trash')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function (): void {
                    Artisan::call('optimize:clear');
                    $this->loadSystemStats();
                    
                    \Filament\Notifications\Notification::make()
                        ->title(__('admin.notifications.cache_cleared'))
                        ->success()
                        ->send();
                }),

            Action::make('optimize')
                ->label(__('admin.actions.optimize'))
                ->icon('heroicon-o-rocket-launch')
                ->color('success')
                ->requiresConfirmation()
                ->action(function (): void {
                    Artisan::call('optimize');
                    $this->loadSystemStats();
                    
                    \Filament\Notifications\Notification::make()
                        ->title(__('admin.notifications.system_optimized'))
                        ->success()
                        ->send();
                }),
        ];
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    private function getSystemUptime(): string
    {
        if (function_exists('sys_getloadavg')) {
            return 'Available';
        }
        return 'N/A';
    }

    private function getDatabaseSize(): string
    {
        try {
            $path = database_path('database.sqlite');
            if (file_exists($path)) {
                return $this->formatBytes(filesize($path));
            }
        } catch (\Exception $e) {
            // Ignore
        }
        return 'N/A';
    }
}
