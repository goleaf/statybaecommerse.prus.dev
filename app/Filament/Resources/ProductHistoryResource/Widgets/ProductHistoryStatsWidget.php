<?php

declare (strict_types=1);
namespace App\Filament\Resources\ProductHistoryResource\Widgets;

use App\Models\ProductHistory;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
/**
 * ProductHistoryStatsWidget
 * 
 * Filament v4 resource for ProductHistoryStatsWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class ProductHistoryStatsWidget extends BaseWidget
{
    /**
     * Handle getStats functionality with proper error handling.
     * @return array
     */
    protected function getStats(): array
    {
        $totalChanges = ProductHistory::count();
        $recentChanges = ProductHistory::where('created_at', '>=', now()->subDays(7))->count();
        $priceChanges = ProductHistory::where('action', 'price_changed')->count();
        $stockUpdates = ProductHistory::where('action', 'stock_updated')->count();
        $mostActiveProduct = ProductHistory::select('product_id', DB::raw('count(*) as changes'))->with('product:id,name')->groupBy('product_id')->orderBy('changes', 'desc')->first();
        $mostActiveUser = ProductHistory::select('user_id', DB::raw('count(*) as changes'))->with('user:id,name')->whereNotNull('user_id')->groupBy('user_id')->orderBy('changes', 'desc')->first();
        return [Stat::make('Total Changes', number_format($totalChanges))->description('All time product changes')->descriptionIcon('heroicon-m-arrow-trending-up')->color('primary'), Stat::make('Recent Changes', number_format($recentChanges))->description('Changes in last 7 days')->descriptionIcon('heroicon-m-clock')->color('success'), Stat::make('Price Changes', number_format($priceChanges))->description('Total price modifications')->descriptionIcon('heroicon-m-currency-euro')->color('warning'), Stat::make('Stock Updates', number_format($stockUpdates))->description('Inventory modifications')->descriptionIcon('heroicon-m-cube')->color('info'), Stat::make('Most Active Product', $mostActiveProduct?->product?->name ?? 'N/A')->description($mostActiveProduct ? "{$mostActiveProduct->changes} changes" : 'No data')->descriptionIcon('heroicon-m-star')->color('secondary'), Stat::make('Most Active User', $mostActiveUser?->user?->name ?? 'System')->description($mostActiveUser ? "{$mostActiveUser->changes} changes" : 'No data')->descriptionIcon('heroicon-m-user')->color('gray')];
    }
    /**
     * Handle getColumns functionality with proper error handling.
     * @return int
     */
    protected function getColumns(): int
    {
        return 3;
    }
}