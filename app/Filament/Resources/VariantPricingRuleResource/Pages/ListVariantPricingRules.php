<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantPricingRuleResource\Pages;

use App\Filament\Resources\VariantPricingRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

final class ListVariantPricingRules extends ListRecords
{
    protected static string $resource = VariantPricingRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('variant_pricing_rules.tabs.all'))
                ->icon('heroicon-o-list-bullet'),

            'active' => Tab::make(__('variant_pricing_rules.tabs.active'))
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->active()),

            'size_based' => Tab::make(__('variant_pricing_rules.tabs.size_based'))
                ->icon('heroicon-o-cube')
                ->modifyQueryUsing(fn (Builder $query) => $query->byType('size_based')),

            'quantity_based' => Tab::make(__('variant_pricing_rules.tabs.quantity_based'))
                ->icon('heroicon-o-archive-box')
                ->modifyQueryUsing(fn (Builder $query) => $query->byType('quantity_based')),

            'customer_group_based' => Tab::make(__('variant_pricing_rules.tabs.customer_group_based'))
                ->icon('heroicon-o-users')
                ->modifyQueryUsing(fn (Builder $query) => $query->byType('customer_group_based')),

            'time_based' => Tab::make(__('variant_pricing_rules.tabs.time_based'))
                ->icon('heroicon-o-clock')
                ->modifyQueryUsing(fn (Builder $query) => $query->byType('time_based')),
        ];
    }
}
