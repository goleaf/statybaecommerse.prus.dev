<?php declare(strict_types=1);

namespace App\Filament\Resources\DiscountConditionResource\Pages;

use App\Filament\Resources\DiscountConditionResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;

final class ListDiscountConditions extends ListRecords
{
    protected static string $resource = DiscountConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back_to_dashboard')
                ->label(__('common.back_to_dashboard'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url('/admin')
                ->tooltip(__('common.back_to_dashboard_tooltip')),
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('discount_conditions.tabs.all'))
                ->icon('heroicon-o-cog-6-tooth'),
            'active' => Tab::make(__('discount_conditions.tabs.active'))
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('is_active', true))
                ->badge(DiscountConditionResource::getModel()::where('is_active', true)->count()),
            'inactive' => Tab::make(__('discount_conditions.tabs.inactive'))
                ->icon('heroicon-o-x-circle')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('is_active', false))
                ->badge(DiscountConditionResource::getModel()::where('is_active', false)->count()),
            'high_priority' => Tab::make(__('discount_conditions.tabs.high_priority'))
                ->icon('heroicon-o-arrow-up')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('priority', '>', 5))
                ->badge(DiscountConditionResource::getModel()::where('priority', '>', 5)->count()),
            'numeric' => Tab::make(__('discount_conditions.tabs.numeric'))
                ->icon('heroicon-o-calculator')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereIn('type', ['cart_total', 'item_qty', 'priority'])),
            'string' => Tab::make(__('discount_conditions.tabs.string'))
                ->icon('heroicon-o-document-text')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereIn('type', ['product', 'category', 'brand', 'collection', 'attribute_value'])),
        ];
    }
}
