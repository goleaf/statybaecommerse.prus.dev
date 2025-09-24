<?php

declare(strict_types=1);
declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\VariantAnalyticsResource\Pages;
use App\Models\VariantAnalytics;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * VariantAnalyticsResource
 *
 * Filament v4 resource for VariantAnalytics management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class VariantAnalyticsResource extends Resource
{
    protected static ?string $model = VariantAnalytics::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static UnitEnum|string|null $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('admin.variant_analytics.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.variant_analytics.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.variant_analytics.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make(__('admin.variant_analytics.tabs'))
                    ->tabs([
                        Tab::make(__('admin.variant_analytics.basic_info'))
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Section::make(__('admin.variant_analytics.basic_info'))
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('variant_id')
                                                    ->label(__('admin.variant_analytics.variant'))
                                                    ->relationship('variant', 'name')
                                                    ->required()
                                                    ->searchable()
                                                    ->preload()
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, callable $set) {
                                                        if ($state) {
                                                            $variant = \App\Models\ProductVariant::find($state);
                                                            if ($variant) {
                                                                $set('variant_name', $variant->name);
                                                                $set('product_name', $variant->product->name ?? '');
                                                            }
                                                        }
                                                    }),
                                                DatePicker::make('date')
                                                    ->label(__('admin.variant_analytics.date'))
                                                    ->required()
                                                    ->default(now())
                                                    ->maxDate(now())
                                                    ->live(),
                                            ]),
                                        Grid::make(2)
                                            ->schema([
                                                Placeholder::make('variant_name')
                                                    ->label(__('admin.variant_analytics.variant_name'))
                                                    ->content(fn ($record) => $record?->variant?->name ?? '')
                                                    ->visible(fn ($record) => $record !== null),
                                                Placeholder::make('product_name')
                                                    ->label(__('admin.variant_analytics.product_name'))
                                                    ->content(fn ($record) => $record?->variant?->product?->name ?? '')
                                                    ->visible(fn ($record) => $record !== null),
                                            ]),
                                    ]),
                            ]),
                        Tab::make(__('admin.variant_analytics.metrics'))
                            ->icon('heroicon-o-chart-bar')
                            ->schema([
                                Section::make(__('admin.variant_analytics.traffic_metrics'))
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('views')
                                                    ->label(__('admin.variant_analytics.views'))
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->default(0)
                                                    ->live()
                                                    ->suffix('views'),
                                                TextInput::make('clicks')
                                                    ->label(__('admin.variant_analytics.clicks'))
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->default(0)
                                                    ->live()
                                                    ->suffix('clicks'),
                                                Placeholder::make('click_through_rate')
                                                    ->label(__('admin.variant_analytics.ctr'))
                                                    ->content(function (callable $get) {
                                                        $views = (float) $get('views');
                                                        $clicks = (float) $get('clicks');
                                                        if ($views > 0) {
                                                            return number_format(($clicks / $views) * 100, 2).'%';
                                                        }

                                                        return '0%';
                                                    }),
                                            ]),
                                    ]),
                                Section::make(__('admin.variant_analytics.conversion_metrics'))
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('add_to_cart')
                                                    ->label(__('admin.variant_analytics.add_to_cart'))
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->default(0)
                                                    ->live()
                                                    ->suffix('adds'),
                                                TextInput::make('purchases')
                                                    ->label(__('admin.variant_analytics.purchases'))
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->default(0)
                                                    ->live()
                                                    ->suffix('purchases'),
                                                TextInput::make('revenue')
                                                    ->label(__('admin.variant_analytics.revenue'))
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->step(0.0001)
                                                    ->default(0)
                                                    ->live()
                                                    ->prefix('€'),
                                            ]),
                                    ]),
                                Section::make(__('admin.variant_analytics.calculated_metrics'))
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                Placeholder::make('add_to_cart_rate')
                                                    ->label(__('admin.variant_analytics.atc_rate'))
                                                    ->content(function (callable $get) {
                                                        $clicks = (float) $get('clicks');
                                                        $addToCart = (float) $get('add_to_cart');
                                                        if ($clicks > 0) {
                                                            return number_format(($addToCart / $clicks) * 100, 2).'%';
                                                        }

                                                        return '0%';
                                                    }),
                                                Placeholder::make('purchase_rate')
                                                    ->label(__('admin.variant_analytics.purchase_rate'))
                                                    ->content(function (callable $get) {
                                                        $addToCart = (float) $get('add_to_cart');
                                                        $purchases = (float) $get('purchases');
                                                        if ($addToCart > 0) {
                                                            return number_format(($purchases / $addToCart) * 100, 2).'%';
                                                        }

                                                        return '0%';
                                                    }),
                                                TextInput::make('conversion_rate')
                                                    ->label(__('admin.variant_analytics.conversion_rate'))
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->maxValue(100)
                                                    ->step(0.0001)
                                                    ->suffix('%')
                                                    ->default(0)
                                                    ->helperText(__('admin.variant_analytics.conversion_rate_help')),
                                            ]),
                                    ]),
                            ]),
                        Tab::make(__('admin.variant_analytics.additional_data'))
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Section::make(__('admin.variant_analytics.additional_info'))
                                    ->schema([
                                        KeyValue::make('additional_metrics')
                                            ->label(__('admin.variant_analytics.additional_metrics'))
                                            ->keyLabel(__('admin.variant_analytics.metric_name'))
                                            ->valueLabel(__('admin.variant_analytics.metric_value'))
                                            ->helperText(__('admin.variant_analytics.additional_metrics_help')),
                                        TextInput::make('notes')
                                            ->label(__('admin.variant_analytics.notes'))
                                            ->maxLength(1000)
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('variant.name')
                    ->label(__('admin.variant_analytics.variant'))
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->copyable()
                    ->description(fn ($record) => $record->variant->product->name ?? ''),
                TextColumn::make('variant.sku')
                    ->label(__('admin.variant_analytics.sku'))
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->copyable(),
                TextColumn::make('date')
                    ->label(__('admin.variant_analytics.date'))
                    ->date()
                    ->sortable()
                    ->toggleable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('views')
                    ->label(__('admin.variant_analytics.views'))
                    ->numeric()
                    ->sortable()
                    ->toggleable()
                    ->color('gray'),
                TextColumn::make('clicks')
                    ->label(__('admin.variant_analytics.clicks'))
                    ->numeric()
                    ->sortable()
                    ->toggleable()
                    ->color('info'),
                TextColumn::make('click_through_rate')
                    ->label(__('admin.variant_analytics.ctr'))
                    ->getStateUsing(fn ($record) => $record->click_through_rate)
                    ->formatStateUsing(fn ($state) => number_format($state, 2).'%')
                    ->sortable(false)
                    ->toggleable()
                    ->badge()
                    ->color(fn ($state) => $state >= 5 ? 'success' : ($state >= 2 ? 'warning' : 'danger')),
                TextColumn::make('add_to_cart')
                    ->label(__('admin.variant_analytics.add_to_cart'))
                    ->numeric()
                    ->sortable()
                    ->toggleable()
                    ->color('warning'),
                TextColumn::make('add_to_cart_rate')
                    ->label(__('admin.variant_analytics.atc_rate'))
                    ->getStateUsing(fn ($record) => $record->add_to_cart_rate)
                    ->formatStateUsing(fn ($state) => number_format($state, 2).'%')
                    ->sortable(false)
                    ->toggleable()
                    ->badge()
                    ->color(fn ($state) => $state >= 20 ? 'success' : ($state >= 10 ? 'warning' : 'danger')),
                TextColumn::make('purchases')
                    ->label(__('admin.variant_analytics.purchases'))
                    ->numeric()
                    ->sortable()
                    ->toggleable()
                    ->color('success'),
                TextColumn::make('purchase_rate')
                    ->label(__('admin.variant_analytics.purchase_rate'))
                    ->getStateUsing(fn ($record) => $record->purchase_rate)
                    ->formatStateUsing(fn ($state) => number_format($state, 2).'%')
                    ->sortable(false)
                    ->toggleable()
                    ->badge()
                    ->color(fn ($state) => $state >= 30 ? 'success' : ($state >= 15 ? 'warning' : 'danger')),
                TextColumn::make('revenue')
                    ->label(__('admin.variant_analytics.revenue'))
                    ->money('EUR')
                    ->sortable()
                    ->toggleable()
                    ->color('success'),
                TextColumn::make('average_revenue_per_purchase')
                    ->label(__('admin.variant_analytics.avg_revenue'))
                    ->getStateUsing(fn ($record) => $record->average_revenue_per_purchase)
                    ->money('EUR')
                    ->sortable(false)
                    ->toggleable()
                    ->color('info'),
                TextColumn::make('conversion_rate')
                    ->label(__('admin.variant_analytics.conversion_rate'))
                    ->formatStateUsing(fn ($state) => number_format($state, 2).'%')
                    ->sortable()
                    ->toggleable()
                    ->badge()
                    ->color(fn ($state) => $state >= 5 ? 'success' : ($state >= 2 ? 'warning' : 'danger')),
                BadgeColumn::make('performance_status')
                    ->label(__('admin.variant_analytics.performance_status'))
                    ->getStateUsing(function ($record) {
                        $conversionRate = $record->conversion_rate;
                        $revenue = $record->revenue;

                        if ($conversionRate >= 5 && $revenue >= 100) {
                            return 'high';
                        } elseif ($conversionRate >= 2 && $revenue >= 50) {
                            return 'medium';
                        } else {
                            return 'low';
                        }
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'high' => __('admin.variant_analytics.high_performing'),
                        'medium' => __('admin.variant_analytics.medium_performing'),
                        'low' => __('admin.variant_analytics.low_performing'),
                        default => __('admin.variant_analytics.unknown')
                    })
                    ->colors([
                        'success' => 'high',
                        'warning' => 'medium',
                        'danger' => 'low',
                    ])
                    ->sortable(false)
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label(__('admin.variant_analytics.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin.variant_analytics.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('variant_id')
                    ->label(__('admin.variant_analytics.variant'))
                    ->relationship('variant', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                SelectFilter::make('product_id')
                    ->label(__('admin.variant_analytics.product'))
                    ->relationship('variant.product', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                DateFilter::make('date')
                    ->label(__('admin.variant_analytics.date')),
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('date_from')
                            ->label(__('admin.variant_analytics.date_from')),
                        DatePicker::make('date_until')
                            ->label(__('admin.variant_analytics.date_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $data['date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
                Filter::make('revenue_range')
                    ->form([
                        TextInput::make('revenue_from')
                            ->label(__('admin.variant_analytics.revenue_from'))
                            ->numeric()
                            ->step(0.01),
                        TextInput::make('revenue_to')
                            ->label(__('admin.variant_analytics.revenue_to'))
                            ->numeric()
                            ->step(0.01),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['revenue_from'],
                                fn (Builder $query, $amount): Builder => $query->where('revenue', '>=', $amount),
                            )
                            ->when(
                                $data['revenue_to'],
                                fn (Builder $query, $amount): Builder => $query->where('revenue', '<=', $amount),
                            );
                    }),
                Filter::make('conversion_rate_range')
                    ->form([
                        TextInput::make('conversion_rate_from')
                            ->label(__('admin.variant_analytics.conversion_rate_from'))
                            ->numeric()
                            ->step(0.01)
                            ->suffix('%'),
                        TextInput::make('conversion_rate_to')
                            ->label(__('admin.variant_analytics.conversion_rate_to'))
                            ->numeric()
                            ->step(0.01)
                            ->suffix('%'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['conversion_rate_from'],
                                fn (Builder $query, $rate): Builder => $query->where('conversion_rate', '>=', $rate),
                            )
                            ->when(
                                $data['conversion_rate_to'],
                                fn (Builder $query, $rate): Builder => $query->where('conversion_rate', '<=', $rate),
                            );
                    }),
                Filter::make('high_performing')
                    ->label(__('admin.variant_analytics.high_performing'))
                    ->query(fn (Builder $query): Builder => $query->where('conversion_rate', '>=', 5.0)),
                Filter::make('medium_performing')
                    ->label(__('admin.variant_analytics.medium_performing'))
                    ->query(fn (Builder $query): Builder => $query->whereBetween('conversion_rate', [2.0, 5.0])),
                Filter::make('low_performing')
                    ->label(__('admin.variant_analytics.low_performing'))
                    ->query(fn (Builder $query): Builder => $query->where('conversion_rate', '<', 2.0)),
                Filter::make('has_purchases')
                    ->label(__('admin.variant_analytics.has_purchases'))
                    ->query(fn (Builder $query): Builder => $query->where('purchases', '>', 0)),
                Filter::make('has_revenue')
                    ->label(__('admin.variant_analytics.has_revenue'))
                    ->query(fn (Builder $query): Builder => $query->where('revenue', '>', 0)),
                TernaryFilter::make('is_recent')
                    ->label(__('admin.variant_analytics.is_recent'))
                    ->placeholder(__('admin.variant_analytics.all_records'))
                    ->trueLabel(__('admin.variant_analytics.last_7_days'))
                    ->falseLabel(__('admin.variant_analytics.older_than_7_days'))
                    ->queries(
                        true: fn (Builder $query) => $query->where('date', '>=', now()->subDays(7)),
                        false: fn (Builder $query) => $query->where('date', '<', now()->subDays(7)),
                    ),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('regenerate_metrics')
                    ->label(__('admin.variant_analytics.regenerate_metrics'))
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (VariantAnalytics $record): void {
                        // Regenerate metrics logic here
                        $record->updateConversionRate();
                        Notification::make()
                            ->title(__('admin.variant_analytics.metrics_regenerated_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Action::make('duplicate')
                    ->label(__('admin.variant_analytics.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->action(function (VariantAnalytics $record): void {
                        $newRecord = $record->replicate();
                        $newRecord->date = now()->toDateString();
                        $newRecord->save();
                        Notification::make()
                            ->title(__('admin.variant_analytics.duplicated_successfully'))
                            ->success()
                            ->send();
                    }),
                Action::make('export_single')
                    ->label(__('admin.variant_analytics.export_single'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function (VariantAnalytics $record): void {
                        // Export single record logic here
                        Notification::make()
                            ->title(__('admin.variant_analytics.exported_successfully'))
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('export_analytics')
                        ->label(__('admin.variant_analytics.export_analytics'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            // Export logic here
                            Notification::make()
                                ->title(__('admin.variant_analytics.exported_successfully'))
                                ->body(__('admin.variant_analytics.exported_count', ['count' => $records->count()]))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('regenerate_metrics_bulk')
                        ->label(__('admin.variant_analytics.regenerate_metrics_bulk'))
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $count = 0;
                            foreach ($records as $record) {
                                $record->updateConversionRate();
                                $count++;
                            }
                            Notification::make()
                                ->title(__('admin.variant_analytics.metrics_regenerated_successfully'))
                                ->body(__('admin.variant_analytics.regenerated_count', ['count' => $count]))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('duplicate_records')
                        ->label(__('admin.variant_analytics.duplicate_records'))
                        ->icon('heroicon-o-document-duplicate')
                        ->color('gray')
                        ->action(function (Collection $records): void {
                            $count = 0;
                            foreach ($records as $record) {
                                $newRecord = $record->replicate();
                                $newRecord->date = now()->toDateString();
                                $newRecord->save();
                                $count++;
                            }
                            Notification::make()
                                ->title(__('admin.variant_analytics.duplicated_successfully'))
                                ->body(__('admin.variant_analytics.duplicated_count', ['count' => $count]))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('reset_metrics')
                        ->label(__('admin.variant_analytics.reset_metrics'))
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('danger')
                        ->action(function (Collection $records): void {
                            $count = 0;
                            foreach ($records as $record) {
                                $record->update([
                                    'views' => 0,
                                    'clicks' => 0,
                                    'add_to_cart' => 0,
                                    'purchases' => 0,
                                    'revenue' => 0,
                                    'conversion_rate' => 0,
                                ]);
                                $count++;
                            }
                            Notification::make()
                                ->title(__('admin.variant_analytics.metrics_reset_successfully'))
                                ->body(__('admin.variant_analytics.reset_count', ['count' => $count]))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('date', 'desc')
            ->poll('30s');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVariantAnalytics::route('/'),
            'create' => Pages\CreateVariantAnalytics::route('/create'),
            'view' => Pages\ViewVariantAnalytics::route('/{record}'),
            'edit' => Pages\EditVariantAnalytics::route('/{record}/edit'),
        ];
    }
}
