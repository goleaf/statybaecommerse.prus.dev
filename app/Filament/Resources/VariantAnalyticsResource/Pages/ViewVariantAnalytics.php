<?php declare(strict_types=1);

namespace App\Filament\Resources\VariantAnalyticsResource\Pages;

use App\Filament\Resources\VariantAnalyticsResource;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\TextEntry;

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

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament::variant_analytics.basic_info'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('variant.name')
                                    ->label(__('filament::variant_analytics.variant'))
                                    ->columnSpan(1),
                                
                                TextEntry::make('date')
                                    ->label(__('filament::variant_analytics.date'))
                                    ->date()
                                    ->columnSpan(1),
                            ]),
                    ]),
                
                Section::make(__('filament::variant_analytics.metrics'))
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('views')
                                    ->label(__('filament::variant_analytics.views'))
                                    ->numeric()
                                    ->columnSpan(1),
                                
                                TextEntry::make('clicks')
                                    ->label(__('filament::variant_analytics.clicks'))
                                    ->numeric()
                                    ->columnSpan(1),
                                
                                TextEntry::make('add_to_cart')
                                    ->label(__('filament::variant_analytics.add_to_cart'))
                                    ->numeric()
                                    ->columnSpan(1),
                                
                                TextEntry::make('purchases')
                                    ->label(__('filament::variant_analytics.purchases'))
                                    ->numeric()
                                    ->columnSpan(1),
                            ]),
                        
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('revenue')
                                    ->label(__('filament::variant_analytics.revenue'))
                                    ->money('EUR')
                                    ->columnSpan(1),
                                
                                TextEntry::make('conversion_rate')
                                    ->label(__('filament::variant_analytics.conversion_rate'))
                                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%')
                                    ->columnSpan(1),
                            ]),
                    ]),
                
                Section::make(__('filament::variant_analytics.calculated_metrics'))
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('click_through_rate')
                                    ->label(__('filament::variant_analytics.ctr'))
                                    ->getStateUsing(fn ($record) => $record->click_through_rate)
                                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%')
                                    ->columnSpan(1),
                                
                                TextEntry::make('add_to_cart_rate')
                                    ->label(__('filament::variant_analytics.atc_rate'))
                                    ->getStateUsing(fn ($record) => $record->add_to_cart_rate)
                                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%')
                                    ->columnSpan(1),
                                
                                TextEntry::make('purchase_rate')
                                    ->label(__('filament::variant_analytics.purchase_rate'))
                                    ->getStateUsing(fn ($record) => $record->purchase_rate)
                                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%')
                                    ->columnSpan(1),
                                
                                TextEntry::make('average_revenue_per_purchase')
                                    ->label(__('filament::variant_analytics.avg_revenue'))
                                    ->getStateUsing(fn ($record) => $record->average_revenue_per_purchase)
                                    ->money('EUR')
                                    ->columnSpan(1),
                            ]),
                    ]),
            ]);
    }
}
