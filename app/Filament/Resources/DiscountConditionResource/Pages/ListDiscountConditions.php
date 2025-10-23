<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountConditionResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\DiscountConditionResource;
use App\Filament\Resources\DiscountConditionResource\Widgets\DiscountConditionChartWidget;
use App\Filament\Resources\DiscountConditionResource\Widgets\DiscountConditionStatsWidget;
use App\Filament\Resources\DiscountConditionResource\Widgets\DiscountConditionTableWidget;
use App\Models\DiscountCondition;
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
            'all'    => Tab::make(__('discount_conditions.tabs.all')),
            'active' => Tab::make(__('discount_conditions.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(fn () => DiscountCondition::query()->where('is_active', true)->count()),
            'inactive' => Tab::make(__('discount_conditions.tabs.inactive'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badge(fn () => DiscountCondition::query()->where('is_active', false)->count()),
            'high_priority' => Tab::make(__('discount_conditions.tabs.high_priority'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('priority')->orderBy('priority')->where('priority', '<=', 3))
                ->badge(fn () => DiscountCondition::query()->whereNotNull('priority')->where('priority', '<=', 3)->count()),
            'low_priority' => Tab::make(__('discount_conditions.tabs.low_priority'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('priority')->orderByDesc('priority')->where('priority', '>=', 7))
                ->badge(fn () => DiscountCondition::query()->whereNotNull('priority')->where('priority', '>=', 7)->count()),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DiscountConditionStatsWidget::class,
            DiscountConditionChartWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            DiscountConditionTableWidget::class,
        ];
    }
}
