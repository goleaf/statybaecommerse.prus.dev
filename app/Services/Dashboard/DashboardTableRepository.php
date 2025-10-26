<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\FailedJob;
use App\Models\Order;
use App\Models\Product;
use App\Models\Scopes\ActiveScope;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

final class DashboardTableRepository
{
    public function recentOrdersQuery(): Builder
    {
        return Order::query()
            ->withoutGlobalScopes([ActiveScope::class])
            ->with('user')
            ->whereNull('deleted_at')
            ->latest('created_at');
    }

    public function lowStockProductsQuery(): Builder
    {
        $threshold = (int) Config::get('inventory.low_stock_threshold', 5);

        return Product::query()
            ->withoutGlobalScopes([ActiveScope::class])
            ->where('manage_stock', true)
            ->whereNull('deleted_at')
            ->where(function ($query) use ($threshold) {
                $query->where(function ($innerQuery) {
                    $innerQuery
                        ->whereNotNull('low_stock_threshold')
                        ->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
                })->orWhere(function ($innerQuery) use ($threshold) {
                    $innerQuery
                        ->whereNull('low_stock_threshold')
                        ->where('stock_quantity', '<=', $threshold);
                });
            })
            ->orderBy('stock_quantity');
    }

    public function recentFailedJobsQuery(): Builder
    {
        return FailedJob::query()
            ->orderByDesc('failed_at');
    }

    public function recentFailedJobs(int $limit = 10): Collection
    {
        return $this->recentFailedJobsQuery()
            ->limit($limit)
            ->get()
            ->map(function (FailedJob $job) {
                return [
                    'id'         => (int) $job->id,
                    'connection' => $job->connection,
                    'queue'      => $job->queue,
                    'failed_at'  => CarbonImmutable::parse((string) $job->failed_at),
                    'job'        => $job->job_name,
                    'exception'  => $job->exception,
                ];
            });
    }
}
