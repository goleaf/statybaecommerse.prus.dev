<?php declare(strict_types=1);

namespace App\Filament\Resources\ProductComparisonResource\Pages;

use App\Filament\Resources\ProductComparisonResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;

class ListProductComparisons extends ListRecords
{
    protected static string $resource = ProductComparisonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('product_comparisons.tabs.all'))
                ->icon('heroicon-m-list-bullet'),
            'today' => Tab::make(__('product_comparisons.tabs.today'))
                ->modifyQueryUsing(fn(Builder $query) => $query->whereDate('created_at', today()))
                ->icon('heroicon-m-calendar-days'),
            'this_week' => Tab::make(__('product_comparisons.tabs.this_week'))
                ->modifyQueryUsing(fn(Builder $query) => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
                ->icon('heroicon-m-calendar'),
            'this_month' => Tab::make(__('product_comparisons.tabs.this_month'))
                ->modifyQueryUsing(fn(Builder $query) => $query->whereMonth('created_at', now()->month))
                ->icon('heroicon-m-calendar'),
        ];
    }
}
