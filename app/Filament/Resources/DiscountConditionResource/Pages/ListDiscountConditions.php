<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountConditionResource\Pages;

use App\Filament\Resources\DiscountConditionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

final class ListDiscountConditions extends ListRecords
{
    protected static string $resource = DiscountConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('discount_conditions.tabs.all')),
            
            'active' => Tab::make(__('discount_conditions.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_active', true)->count()),
            
            'minimum_amount' => Tab::make(__('discount_conditions.tabs.minimum_amount'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'minimum_amount'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'minimum_amount')->count()),
            
            'minimum_quantity' => Tab::make(__('discount_conditions.tabs.minimum_quantity'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'minimum_quantity'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'minimum_quantity')->count()),
            
            'customer_group' => Tab::make(__('discount_conditions.tabs.customer_group'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'customer_group'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'customer_group')->count()),
            
            'product_category' => Tab::make(__('discount_conditions.tabs.product_category'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'product_category'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'product_category')->count()),
            
            'date_range' => Tab::make(__('discount_conditions.tabs.date_range'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'date_range'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'date_range')->count()),
            
            'current' => Tab::make(__('discount_conditions.tabs.current'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('valid_from', '<=', now())->where(function ($q) {
                    $q->whereNull('valid_until')->orWhere('valid_until', '>=', now());
                }))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('valid_from', '<=', now())->where(function ($q) {
                    $q->whereNull('valid_until')->orWhere('valid_until', '>=', now());
                })->count()),
        ];
    }
}
