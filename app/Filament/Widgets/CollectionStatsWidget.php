<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Collection;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

final class CollectionStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalCollections = Collection::count();
        $visibleCollections = Collection::where('is_visible', true)->count();
        $automaticCollections = Collection::where('is_automatic', true)->count();
        $manualCollections = Collection::where('is_automatic', false)->count();

        // Get collections with products
        $collectionsWithProducts = Collection::has('products')->count();

        // Get total products in all collections
        $totalProductsInCollections = DB::table('product_collections')->count();

        // Get average products per collection
        $avgProductsPerCollection = $collectionsWithProducts > 0
            ? round($totalProductsInCollections / $collectionsWithProducts, 1)
            : 0;

        return [
            Stat::make(__('admin.collections.stats.total_collections'), $totalCollections)
                ->description(__('admin.collections.stats.all_collections'))
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('primary'),

            Stat::make(__('admin.collections.stats.visible_collections'), $visibleCollections)
                ->description(__('admin.collections.stats.visible_to_customers'))
                ->descriptionIcon('heroicon-m-eye')
                ->color('success'),

            Stat::make(__('admin.collections.stats.automatic_collections'), $automaticCollections)
                ->description(__('admin.collections.stats.auto_generated'))
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color('info'),

            Stat::make(__('admin.collections.stats.manual_collections'), $manualCollections)
                ->description(__('admin.collections.stats.manually_created'))
                ->descriptionIcon('heroicon-m-hand-raised')
                ->color('warning'),

            Stat::make(__('admin.collections.stats.collections_with_products'), $collectionsWithProducts)
                ->description(__('admin.collections.stats.have_products'))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success'),

            Stat::make(__('admin.collections.stats.avg_products_per_collection'), $avgProductsPerCollection)
                ->description(__('admin.collections.stats.average_products'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('gray'),
        ];
    }
}
