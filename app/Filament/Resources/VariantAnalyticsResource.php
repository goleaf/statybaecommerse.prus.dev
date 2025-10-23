<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\VariantAnalyticsResource\Pages;
use App\Models\ProductVariant;
use App\Models\VariantAnalytics;
use App\Support\Filament\Components\Flatpickr;
use BackedEnum;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use BackedEnum;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Filters\Filter;
use BackedEnum;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * VariantAnalyticsResource
 *
 * Filament v4 resource for VariantAnalytics management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class VariantAnalyticsResource extends Resource
{
    protected static ?string $model = VariantAnalytics::class;

    /**
     * @var string|BackedEnum|null
     */
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationGroup = null;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::Inventory->label();
    }

    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-chart-bar-square';
    }

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

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-chart-bar-square';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make(__('admin.variant_analytics.tabs'))
                    ->tabs([
                        Forms\Components\Tabs\Tab::make(__('admin.variant_analytics.basic_info'))
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\Section::make(__('admin.variant_analytics.basic_info'))
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Select::make('variant_id')
                                                    ->label(__('admin.variant_analytics.variant'))
                                                    ->relationship('variant', 'name')
                                                    ->required()
                                                    ->searchable()
                                                    ->preload()
                                                    ->live()
                                                    ->afterStateUpdated(static function (int|string|null $state, callable $set): void {
                                                        if ($state === null || $state === '') {
                                                            return;
                                                        }

                                                        $variant = \App\Models\ProductVariant::find($state);
                                                        if ($variant === null) {
                                                            return;
                                                        }

                                                        $set('variant_name', $variant->name);
                                                        $set('product_name', $variant->product->name ?? '');
                                                    }),
                                                Flatpickr::makeDate('date')
                                                    ->label(__('admin.variant_analytics.date'))
                                                    ->required()
                                                    ->default(now())
                                                    ->maxDate(now())
                                                    ->live(),
                                            ]),
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Placeholder::make('variant_name')
                                                    ->label(__('admin.variant_analytics.variant_name'))
                                                    ->content(static fn (?VariantAnalytics $record): string => $record?->variant?->name ?? '')
                                                    ->visible(static fn (?VariantAnalytics $record): bool => $record !== null),
                                                Placeholder::make('product_name')
                                                    ->label(__('admin.variant_analytics.product_name'))
                                                    ->content(static fn (?VariantAnalytics $record): string => $record?->variant?->product?->name ?? '')
                                                    ->visible(static fn (?VariantAnalytics $record): bool => $record !== null),
                                            ]),
                                    ]),
                            ]),
                        Forms\Components\Tabs\Tab::make(__('admin.variant_analytics.metrics'))
                            ->icon('heroicon-o-chart-bar')
                            ->schema([
                                Forms\Components\Section::make(__('admin.variant_analytics.traffic_metrics'))
                                    ->schema([
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('views')
                                                    ->label(__('admin.variant_analytics.views'))
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->default(0)
                                                    ->live()
                                                    ->suffix('views'),
                                                Forms\Components\TextInput::make('clicks')
                                                    ->label(__('admin.variant_analytics.clicks'))
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->default(0)
                                                    ->live()
                                                    ->suffix('clicks'),
                                                Forms\Components\Placeholder::make('click_through_rate')
                                                    ->label(__('admin.variant_analytics.ctr'))
                                                    ->content(static function (callable $get): string {
                                                        $views = (float) $get('views');
                                                        $clicks = (float) $get('clicks');

                                                        if ($views > 0.0) {
                                                            return number_format(($clicks / $views) * 100, 2) . '%';
                                                        }

                                                        // Ensure even the zero state flows through the same formatter pipeline.
                                                        return FilamentNumber::format(0, 2) . '%';
                                                    }),
                                            ]),
                                    ]),
                                Forms\Components\Section::make(__('admin.variant_analytics.conversion_metrics'))
                                    ->schema([
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('add_to_cart')
                                                    ->label(__('admin.variant_analytics.add_to_cart'))
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->default(0)
                                                    ->live()
                                                    ->suffix('adds'),
                                                Forms\Components\TextInput::make('purchases')
                                                    ->label(__('admin.variant_analytics.purchases'))
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->default(0)
                                                    ->live()
                                                    ->suffix('purchases'),
                                                Forms\Components\TextInput::make('revenue')
                                                    ->label(__('admin.variant_analytics.revenue'))
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->step(0.0001)
                                                    ->default(0)
                                                    ->live()
                                                    ->prefix('€'),
                                            ]),
                                    ]),
                                Forms\Components\Section::make(__('admin.variant_analytics.calculated_metrics'))
                                    ->schema([
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\Placeholder::make('add_to_cart_rate')
                                                    ->label(__('admin.variant_analytics.atc_rate'))
                                                    ->content(static function (callable $get): string {
                                                        $clicks = (float) $get('clicks');
                                                        $addToCart = (float) $get('add_to_cart');

                                                        if ($clicks > 0.0) {
                                                            return number_format(($addToCart / $clicks) * 100, 2) . '%';
                                                        }

                                                        // Ensure even the zero state flows through the same formatter pipeline.
                                                        return FilamentNumber::format(0, 2) . '%';
                                                    }),
                                                Forms\Components\Placeholder::make('purchase_rate')
                                                    ->label(__('admin.variant_analytics.purchase_rate'))
                                                    ->content(static function (callable $get): string {
                                                        $addToCart = (float) $get('add_to_cart');
                                                        $purchases = (float) $get('purchases');

                                                        if ($addToCart > 0.0) {
                                                            return number_format(($purchases / $addToCart) * 100, 2) . '%';
                                                        }

                                                        // Ensure even the zero state flows through the same formatter pipeline.
                                                        return FilamentNumber::format(0, 2) . '%';
                                                    }),
                                                Forms\Components\TextInput::make('conversion_rate')
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
                        Forms\Components\Tabs\Tab::make(__('admin.variant_analytics.additional_data'))
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Forms\Components\Section::make(__('admin.variant_analytics.additional_info'))
                                    ->schema([
                                        Forms\Components\KeyValue::make('additional_metrics')
                                            ->label(__('admin.variant_analytics.additional_metrics'))
                                            ->keyLabel(__('admin.variant_analytics.metric_name'))
                                            ->valueLabel(__('admin.variant_analytics.metric_value'))
                                            ->helperText(__('admin.variant_analytics.additional_metrics_help')),
                                        Forms\Components\TextInput::make('notes')
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
                Tables\Columns\TextColumn::make('variant.name')
                    ->label(__('admin.variant_analytics.variant'))
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->copyable()
                    ->description(static fn (?VariantAnalytics $record): ?string => $record?->variant?->product?->name),
                TextColumn::make('variant.sku')
                    ->label(__('admin.variant_analytics.sku'))
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('date')
                    ->label(__('admin.variant_analytics.date'))
                    ->date()
                    ->sortable()
                    ->toggleable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('views')
                    ->label(__('admin.variant_analytics.views'))
                    ->numeric()
                    ->sortable()
                    ->toggleable()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('clicks')
                    ->label(__('admin.variant_analytics.clicks'))
                    ->numeric()
                    ->sortable()
                    ->toggleable()
                    ->color('info'),
                Tables\Columns\TextColumn::make('click_through_rate')
                    ->label(__('admin.variant_analytics.ctr'))
                    ->getStateUsing(static fn (VariantAnalytics $record): float => (float) $record->click_through_rate)
                    ->formatStateUsing(static fn (float|int|null $state): string => number_format((float) $state, 2) . '%')
                    ->sortable(false)
                    ->toggleable()
                    ->badge()
                    ->color(static function (float|int|null $state): string {
                        if ($state === null) {
                            return 'gray';
                        }

                        if ($state >= 5) {
                            return 'success';
                        }

                        if ($state >= 2) {
                            return 'warning';
                        }

                        return 'danger';
                    }),
                TextColumn::make('add_to_cart')
                    ->label(__('admin.variant_analytics.add_to_cart'))
                    ->numeric()
                    ->sortable()
                    ->toggleable()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('add_to_cart_rate')
                    ->label(__('admin.variant_analytics.atc_rate'))
                    ->getStateUsing(static fn (VariantAnalytics $record): float => (float) $record->add_to_cart_rate)
                    ->formatStateUsing(static fn (float|int|null $state): string => number_format((float) $state, 2) . '%')
                    ->sortable(false)
                    ->toggleable()
                    ->badge()
                    ->color(static function (float|int|null $state): string {
                        if ($state === null) {
                            return 'gray';
                        }

                        if ($state >= 20) {
                            return 'success';
                        }

                        if ($state >= 10) {
                            return 'warning';
                        }

                        return 'danger';
                    }),
                TextColumn::make('purchases')
                    ->label(__('admin.variant_analytics.purchases'))
                    ->numeric()
                    ->sortable()
                    ->toggleable()
                    ->color('success'),
                Tables\Columns\TextColumn::make('purchase_rate')
                    ->label(__('admin.variant_analytics.purchase_rate'))
                    ->getStateUsing(static fn (VariantAnalytics $record): float => (float) $record->purchase_rate)
                    ->formatStateUsing(static fn (float|int|null $state): string => number_format((float) $state, 2) . '%')
                    ->sortable(false)
                    ->toggleable()
                    ->badge()
                    ->color(static function (float|int|null $state): string {
                        if ($state === null) {
                            return 'gray';
                        }

                        if ($state >= 30) {
                            return 'success';
                        }

                        if ($state >= 15) {
                            return 'warning';
                        }

                        return 'danger';
                    }),
                TextColumn::make('revenue')
                    ->label(__('admin.variant_analytics.revenue'))
                    ->money('EUR')
                    ->sortable()
                    ->toggleable()
                    ->color('success'),
                Tables\Columns\TextColumn::make('average_revenue_per_purchase')
                    ->label(__('admin.variant_analytics.avg_revenue'))
                    ->getStateUsing(static fn (VariantAnalytics $record): float => (float) $record->average_revenue_per_purchase)
                    ->money('EUR')
                    ->sortable(false)
                    ->toggleable()
                    ->color('info'),
                Tables\Columns\TextColumn::make('conversion_rate')
                    ->label(__('admin.variant_analytics.conversion_rate'))
                    ->formatStateUsing(static fn (float|int|null $state): string => number_format((float) $state, 2) . '%')
                    ->sortable()
                    ->toggleable()
                    ->badge()
                    ->color(static function (float|int|null $state): string {
                        if ($state === null) {
                            return 'gray';
                        }

                        if ($state >= 5) {
                            return 'success';
                        }

                        if ($state >= 2) {
                            return 'warning';
                        }

                        return 'danger';
                    }),
                BadgeColumn::make('performance_status')
                    ->label(__('admin.variant_analytics.performance_status'))
                    ->getStateUsing(static function (VariantAnalytics $record): string {
                        $conversionRate = (float) $record->conversion_rate;
                        $revenue = (float) $record->revenue;

                        if ($conversionRate >= 5 && $revenue >= 100) {
                            return 'high';
                        }

                        if ($conversionRate >= 2 && $revenue >= 50) {
                            return 'medium';
                        }

                        return 'low';
                    })
                    ->formatStateUsing(static function (?string $state): string {
                        return match ($state) {
                            'high'   => __('admin.variant_analytics.high_performing'),
                            'medium' => __('admin.variant_analytics.medium_performing'),
                            'low'    => __('admin.variant_analytics.low_performing'),
                            default  => __('admin.variant_analytics.unknown'),
                        };
                    })
                    ->colors([
                        'success' => 'high',
                        'warning' => 'medium',
                        'danger'  => 'low',
                    ])
                    ->sortable(false)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.variant_analytics.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('admin.variant_analytics.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('variant_id')
                    ->label(__('admin.variant_analytics.variant'))
                    ->relationship('variant', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                Tables\Filters\SelectFilter::make('product_id')
                    ->label(__('admin.variant_analytics.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                Tables\Filters\DateFilter::make('date')
                    ->label(__('admin.variant_analytics.date')),
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Flatpickr::makeDate('date_from')
                            ->label(__('admin.variant_analytics.date_from')),
                        Flatpickr::makeDate('date_until')
                            ->label(__('admin.variant_analytics.date_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        [$dateFrom, $dateUntil] = DateRange::extract($data, 'date_from', 'date_until');

                        return $query
                            ->when(
                                $dateFrom,
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '>=', $date),
                            )
                            ->when(
                                $dateUntil,
                                fn (Builder $query, $date): Builder => $query->whereDate('date', '<=', $date),
                            );
                    }),
                Tables\Filters\Filter::make('revenue_range')
                    ->form([
                        Forms\Components\TextInput::make('revenue_from')
                            ->label(__('admin.variant_analytics.revenue_from'))
                            ->numeric()
                            ->step(0.01),
                        Forms\Components\TextInput::make('revenue_to')
                            ->label(__('admin.variant_analytics.revenue_to'))
                            ->numeric()
                            ->step(0.01),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $revenueFrom = $data['revenue_from'] ?? null;
                        $revenueTo = $data['revenue_to'] ?? null;

                        return $query
                            ->when(
                                $revenueFrom,
                                fn (Builder $query, $amount): Builder => $query->where('revenue', '>=', $amount),
                            )
                            ->when(
                                $revenueTo,
                                fn (Builder $query, $amount): Builder => $query->where('revenue', '<=', $amount),
                            );
                    }),
                Tables\Filters\Filter::make('conversion_rate_range')
                    ->form([
                        Forms\Components\TextInput::make('conversion_rate_from')
                            ->label(__('admin.variant_analytics.conversion_rate_from'))
                            ->numeric()
                            ->step(0.01)
                            ->suffix('%'),
                        Forms\Components\TextInput::make('conversion_rate_to')
                            ->label(__('admin.variant_analytics.conversion_rate_to'))
                            ->numeric()
                            ->step(0.01)
                            ->suffix('%'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $conversionRateFrom = $data['conversion_rate_from'] ?? null;
                        $conversionRateTo = $data['conversion_rate_to'] ?? null;

                        return $query
                            ->when(
                                $conversionRateFrom,
                                fn (Builder $query, $rate): Builder => $query->where('conversion_rate', '>=', $rate),
                            )
                            ->when(
                                $conversionRateTo,
                                fn (Builder $query, $rate): Builder => $query->where('conversion_rate', '<=', $rate),
                            );
                    }),
                Tables\Filters\Filter::make('high_performing')
                    ->label(__('admin.variant_analytics.high_performing'))
                    ->query(fn (Builder $query): Builder => $query->where('conversion_rate', '>=', 5.0)),
                Tables\Filters\Filter::make('medium_performing')
                    ->label(__('admin.variant_analytics.medium_performing'))
                    ->query(fn (Builder $query): Builder => $query->whereBetween('conversion_rate', [2.0, 5.0])),
                Tables\Filters\Filter::make('low_performing')
                    ->label(__('admin.variant_analytics.low_performing'))
                    ->query(fn (Builder $query): Builder => $query->where('conversion_rate', '<', 2.0)),
                Tables\Filters\Filter::make('has_purchases')
                    ->label(__('admin.variant_analytics.has_purchases'))
                    ->query(fn (Builder $query): Builder => $query->where('purchases', '>', 0)),
                Tables\Filters\Filter::make('has_revenue')
                    ->label(__('admin.variant_analytics.has_revenue'))
                    ->query(fn (Builder $query): Builder => $query->where('revenue', '>', 0)),
                Tables\Filters\TernaryFilter::make('is_recent')
                    ->label(__('admin.variant_analytics.is_recent'))
                    ->placeholder(__('admin.variant_analytics.all_records'))
                    ->trueLabel(__('admin.variant_analytics.last_7_days'))
                    ->falseLabel(__('admin.variant_analytics.older_than_7_days'))
                    ->queries(
                        true: static fn (Builder $query): Builder => $query->where('date', '>=', now()->subDays(7)),
                        false: static fn (Builder $query): Builder => $query->where('date', '<', now()->subDays(7)),
                    ),
            ])
            // Leverage the Tables\Actions namespace to stay aligned with Filament v4 conventions during table configuration.
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('regenerate_metrics')
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
                Tables\Actions\Action::make('duplicate')
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
                Tables\Actions\Action::make('export_single')
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
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('export_analytics')
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
                    Tables\Actions\BulkAction::make('regenerate_metrics_bulk')
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
                    Tables\Actions\BulkAction::make('duplicate_records')
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
                    Tables\Actions\BulkAction::make('reset_metrics')
                        ->label(__('admin.variant_analytics.reset_metrics'))
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('danger')
                        ->action(function (Collection $records): void {
                            $count = 0;
                            foreach ($records as $record) {
                                $record->update([
                                    'views'           => 0,
                                    'clicks'          => 0,
                                    'add_to_cart'     => 0,
                                    'purchases'       => 0,
                                    'revenue'         => 0,
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
            'index'  => Pages\ListVariantAnalytics::route('/'),
            'create' => Pages\CreateVariantAnalytics::route('/create'),
            'view'   => Pages\ViewVariantAnalytics::route('/{record}'),
            'edit'   => Pages\EditVariantAnalytics::route('/{record}/edit'),
        ];
    }
}
