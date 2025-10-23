<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountConditionResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\DiscountConditionResource;
use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;

final class ListDiscountConditions extends BaseListRecords
{
        use HasWidgetTabs;

    protected static string $resource = DiscountConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getWidgetTabs(): array
    {
        return [
            'all'    => SchemaTab::make(__('discount_conditions.tabs.all')),
            'active' => SchemaTab::make(__('discount_conditions.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('is_active', true)->count()),
            'minimum_amount' => WidgetTab::make(__('discount_conditions.tabs.minimum_amount'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'minimum_amount'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('type', 'minimum_amount')->count()),
            'minimum_quantity' => WidgetTab::make(__('discount_conditions.tabs.minimum_quantity'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'minimum_quantity'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('type', 'minimum_quantity')->count()),
            'customer_group' => WidgetTab::make(__('discount_conditions.tabs.customer_group'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'customer_group'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('type', 'customer_group')->count()),
            'product_category' => WidgetTab::make(__('discount_conditions.tabs.product_category'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'product_category'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('type', 'product_category')->count()),
            'date_range' => WidgetTab::make(__('discount_conditions.tabs.date_range'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'date_range'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('type', 'date_range')->count()),
            'current' => WidgetTab::make(__('discount_conditions.tabs.current'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('valid_from', '<=', now())->where(function ($q): void {
                    $q->whereNull('valid_until')->orWhere('valid_until', '>=', now());
                }))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('valid_from', '<=', now())->where(function ($q): void {
                    $q->whereNull('valid_until')->orWhere('valid_until', '>=', now());
                })->count()),
        ];
    }
}
