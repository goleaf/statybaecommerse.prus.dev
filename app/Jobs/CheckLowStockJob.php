<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\LazyCollection;

/**
 * CheckLowStockJob
 *
 * Queue job for CheckLowStockJob background processing with proper error handling, retry logic, and progress tracking.
 */
final class CheckLowStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of job attempts before failing.
     */
    public int $tries = 2;

    /**
     * Define retry backoff windows (in seconds).
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 300];
    }

    /**
     * Handle the job, event, or request processing.
     */
    public function handle(): void
    {
        Log::info('Starting low stock check...');
        // Use LazyCollection with timeout to prevent long-running operations
        $timeout = now()->addMinutes(5);
        // 5 minute timeout for low stock checks
        $lowStockProducts = Product::where('is_visible', true)->where('manage_stock', true)->where('stock_quantity', '<=', DB::raw('low_stock_threshold'))->whereDoesntHave('notifications', function ($query): void {
            $query->where('type', LowStockAlert::class)->where('created_at', '>=', now()->subHours(24));
        })->cursor()->takeUntilTimeout($timeout);
        $processedCount = 0;
        $alertCount = 0;
        // Get admin users with inventory management permissions
        $adminUsers = User::whereHas('roles', function ($query): void {
            $query->whereHas('permissions', function ($q): void {
                $q->where('name', 'manage_inventory');
            });
        })->get();
        if ($adminUsers->isEmpty()) {
            // Fallback to users with admin role
            $adminUsers = User::whereHas('roles', function ($query): void {
                $query->where('name', 'admin');
            })->get();
        }
        foreach ($lowStockProducts as $product) {
            $processedCount++;
            foreach ($adminUsers as $admin) {
                $admin->notify(new LowStockAlert($product));
                $alertCount++;
            }
            Log::info("Low stock alert sent for product: {$product->name} (Stock: {$product->stock_quantity})");
        }
        if ($processedCount === 0) {
            Log::info('No low stock products found.');
        } else {
            Log::info("Low stock check completed. Processed {$processedCount} products, sent {$alertCount} alerts.");
        }
    }
}
