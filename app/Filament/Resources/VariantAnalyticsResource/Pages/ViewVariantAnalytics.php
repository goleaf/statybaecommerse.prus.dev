<?php

declare(strict_types=1);

namespace App\Filament\Resources\VariantAnalyticsResource\Pages;

use App\Filament\Resources\VariantAnalyticsResource;
use App\Models\VariantAnalytics;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Schema;

final class ViewVariantAnalytics extends ViewRecord
{
    protected static string $resource = VariantAnalyticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema|array
    {
        return $schema
            ->components([
                Section::make(__('admin.variant_analytics.basic_info'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('variant.name')
                                    ->label(__('admin.variant_analytics.variant'))
                                    ->columnSpan(1),

                                TextEntry::make('date')
                                    ->label(__('admin.variant_analytics.date'))
                                    ->date()
                                    ->columnSpan(1),
                            ]),
                    ]),

                Section::make(__('admin.variant_analytics.metrics'))
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('views')
                                    ->label(__('admin.variant_analytics.views'))
                                    ->numeric()
                                    ->columnSpan(1),

                                TextEntry::make('clicks')
                                    ->label(__('admin.variant_analytics.clicks'))
                                    ->numeric()
                                    ->columnSpan(1),

                                TextEntry::make('add_to_cart')
                                    ->label(__('admin.variant_analytics.add_to_cart'))
                                    ->numeric()
                                    ->columnSpan(1),

                                TextEntry::make('purchases')
                                    ->label(__('admin.variant_analytics.purchases'))
                                    ->numeric()
                                    ->columnSpan(1),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('revenue')
                                    ->label(__('admin.variant_analytics.revenue'))
                                    ->money('EUR')
                                    ->columnSpan(1),

                                TextEntry::make('conversion_rate')
                                    ->label(__('admin.variant_analytics.conversion_rate'))
                                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%')
                                    ->columnSpan(1),
                            ]),
                    ]),

                Section::make(__('admin.variant_analytics.calculated_metrics'))
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('click_through_rate')
                                    ->label(__('admin.variant_analytics.ctr'))
                                    ->getStateUsing(fn (VariantAnalytics $record) => $record->click_through_rate)
                                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%')
                                    ->columnSpan(1),

                                TextEntry::make('add_to_cart_rate')
                                    ->label(__('admin.variant_analytics.atc_rate'))
                                    ->getStateUsing(fn (VariantAnalytics $record) => $record->add_to_cart_rate)
                                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%')
                                    ->columnSpan(1),

                                TextEntry::make('purchase_rate')
                                    ->label(__('admin.variant_analytics.purchase_rate'))
                                    ->getStateUsing(fn (VariantAnalytics $record) => $record->purchase_rate)
                                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%')
                                    ->columnSpan(1),

                                TextEntry::make('average_revenue_per_purchase')
                                    ->label(__('admin.variant_analytics.avg_revenue'))
                                    ->getStateUsing(fn (VariantAnalytics $record) => $record->average_revenue_per_purchase)
                                    ->money('EUR')
                                    ->columnSpan(1),
                            ]),
                    ]),
            ]);
    }
}
