<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use BackedEnum;
use UnitEnum;
use App\Filament\Resources\VariantPriceHistoryResource\Pages;
use App\Models\VariantPriceHistory;
use App\Support\Filament\Components\Flatpickr;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class VariantPriceHistoryResource extends Resource
{
    use HasNav;

    protected static ?string $model = VariantPriceHistory::class;

    /**
     * Navigation icon override (string|\BackedEnum|null) for Filament v4 alignment.
     */
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-currency-euro';

    /** @var UnitEnum|string|null Navigation grouping centralized via enum. */
    protected static UnitEnum|string|null $navigationGroup = NavigationGroup::System;

    protected static ?int $navigationSort = 20;

    public static function getNavigationGroup(): ?string
    {
        // Resolve enum-backed navigation label so the sidebar remains localized.
        $group = static::$navigationGroup;

        return $group instanceof NavigationGroup ? $group->label() : $group;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('variant_id')
                    ->relationship('variant', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('old_price')
                    ->label('Old Price')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.0001)
                    // Require the legacy price for audit trails and Filament 4 callbacks.
                    ->required(),
                Forms\Components\TextInput::make('new_price')
                    ->label('New Price')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.0001)
                    ->required(),
                Forms\Components\Select::make('price_type')
                    ->options([
                        'regular'   => 'Regular Price',
                        'sale'      => 'Sale Price',
                        'wholesale' => 'Wholesale Price',
                        'bulk'      => 'Bulk Price',
                    ])
                    ->default('regular')
                    ->required(),
                Forms\Components\Select::make('change_reason')
                    ->options([
                        'manual'            => 'Manual Change',
                        'automatic'         => 'Automatic Update',
                        'promotion'         => 'Promotion',
                        'cost_change'       => 'Cost Change',
                        'market_adjustment' => 'Market Adjustment',
                        'seasonal'          => 'Seasonal Change',
                    ])
                    ->default('manual')
                    ->required(),
                Forms\Components\Select::make('changed_by')
                    ->relationship('changedBy', 'name')
                    ->searchable()
                    ->preload(),
                Flatpickr::makeDateTime('effective_from')
                    ->label('Effective From')
                    ->required(),
                Flatpickr::makeDateTime('effective_until')
                    ->label('Effective Until')
                    ->after('effective_from'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('variant.name')
                    ->label('Variant')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('old_price')
                    ->label('Old Price')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('new_price')
                    ->label('New Price')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_change')
                    ->label('Change')
                    // Use strict typing to keep Filament 4 column callbacks predictable.
                    ->state(static function (VariantPriceHistory $record): ?float {
                        if ($record->old_price === null || $record->new_price === null) {
                            return null;
                        }

                        return (float) ($record->new_price - $record->old_price);
                    })
                    ->formatStateUsing(static function (?float $state, VariantPriceHistory $record): string {
                        if ($state === null) {
                            return '-';
                        }

                        $percentage = $record->old_price > 0
                            ? ($state / $record->old_price) * 100
                            : 0.0;

                        $sign = $state >= 0 ? '+' : '';

                        return sprintf(
                            '%s€%s (%s%s%%)',
                            $sign,
                            number_format($state, 2),
                            $sign,
                            number_format($percentage, 1)
                        );
                    })
                    ->sortable(query: static function (Builder $query, string $direction): Builder {
                        $direction = $direction === 'asc' ? 'asc' : 'desc';

                        return $query->orderByRaw(
                            '(COALESCE(new_price, 0) - COALESCE(old_price, 0)) ' . $direction
                        );
                    })
                    ->color(static fn (VariantPriceHistory $record): string => $record->isIncrease() ? 'success' : ($record->isDecrease() ? 'danger' : 'gray')),
                Tables\Columns\TextColumn::make('price_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'regular'   => 'primary',
                        'sale'      => 'success',
                        'wholesale' => 'warning',
                        'bulk'      => 'info',
                        default     => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('change_reason')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'manual'            => 'primary',
                        'automatic'         => 'success',
                        'promotion'         => 'warning',
                        'cost_change'       => 'info',
                        'market_adjustment' => 'danger',
                        'seasonal'          => 'secondary',
                        default             => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('changedBy.name')
                    ->label('Changed By')
                    ->sortable(),
                Tables\Columns\TextColumn::make('effective_from')
                    ->label('Effective From')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('effective_until')
                    ->label('Effective Until')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('price_type')
                    ->options([
                        'regular'   => 'Regular Price',
                        'sale'      => 'Sale Price',
                        'wholesale' => 'Wholesale Price',
                        'bulk'      => 'Bulk Price',
                    ]),
                Tables\Filters\SelectFilter::make('change_reason')
                    ->options([
                        'manual'            => 'Manual Change',
                        'automatic'         => 'Automatic Update',
                        'promotion'         => 'Promotion',
                        'cost_change'       => 'Cost Change',
                        'market_adjustment' => 'Market Adjustment',
                        'seasonal'          => 'Seasonal Change',
                    ]),
                Tables\Filters\SelectFilter::make('variant_id')
                    ->relationship('variant', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('effective_date_range')
                    ->form([
                        Flatpickr::makeDate('effective_from')
                            ->label('Effective From'),
                        Flatpickr::makeDate('effective_until')
                            ->label('Effective Until'),
                    ])
                    ->query(static function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['effective_from'] ?? null,
                                static fn (Builder $builder, string $date): Builder => $builder->whereDate('effective_from', '>=', $date),
                            )
                            ->when(
                                $data['effective_until'] ?? null,
                                static fn (Builder $builder, string $date): Builder => $builder->whereDate('effective_until', '<=', $date),
                            );
                    }),
                Tables\Filters\TernaryFilter::make('price_change')
                    ->label('Price Change')
                    ->placeholder('All changes')
                    ->trueLabel('Increases only')
                    ->falseLabel('Decreases only')
                    ->queries(
                        true: static fn (Builder $query): Builder => $query->increases(),
                        false: static fn (Builder $query): Builder => $query->decreases(),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('effective_from', 'desc')
            ->searchable(['variant.name'])
            ->paginated([10, 25, 50, 100]);
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
            'index'  => Pages\ListVariantPriceHistories::route('/'),
            'create' => Pages\CreateVariantPriceHistory::route('/create'),
            'view'   => Pages\ViewVariantPriceHistory::route('/{record}'),
            'edit'   => Pages\EditVariantPriceHistory::route('/{record}/edit'),
        ];
    }
}
