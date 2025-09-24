<?php

declare(strict_types=1);
declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\VariantPriceHistoryResource\Pages;
use App\Models\VariantPriceHistory;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

final class VariantPriceHistoryResource extends Resource
{
    protected static ?string $model = VariantPriceHistory::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-currency-euro';

    protected static UnitEnum|string|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return $schema
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
                    ->step(0.0001),
                Forms\Components\TextInput::make('new_price')
                    ->label('New Price')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.0001)
                    ->required(),
                Forms\Components\Select::make('price_type')
                    ->options([
                        'regular' => 'Regular Price',
                        'sale' => 'Sale Price',
                        'wholesale' => 'Wholesale Price',
                        'bulk' => 'Bulk Price',
                    ])
                    ->default('regular')
                    ->required(),
                Forms\Components\Select::make('change_reason')
                    ->options([
                        'manual' => 'Manual Change',
                        'automatic' => 'Automatic Update',
                        'promotion' => 'Promotion',
                        'cost_change' => 'Cost Change',
                        'market_adjustment' => 'Market Adjustment',
                        'seasonal' => 'Seasonal Change',
                    ])
                    ->default('manual')
                    ->required(),
                Forms\Components\Select::make('changed_by')
                    ->relationship('changedBy', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\DateTimePicker::make('effective_from')
                    ->label('Effective From')
                    ->required(),
                Forms\Components\DateTimePicker::make('effective_until')
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
                    ->formatStateUsing(function ($record) {
                        if ($record->old_price && $record->new_price) {
                            $change = $record->new_price - $record->old_price;
                            $percentage = $record->old_price > 0 ? ($change / $record->old_price) * 100 : 0;
                            $sign = $change >= 0 ? '+' : '';

                            return $sign.'€'.number_format($change, 2).' ('.$sign.number_format($percentage, 1).'%)';
                        }

                        return '-';
                    })
                    ->sortable()
                    ->color(fn ($record) => $record->isIncrease() ? 'success' : ($record->isDecrease() ? 'danger' : 'gray')),
                Tables\Columns\TextColumn::make('price_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'regular' => 'primary',
                        'sale' => 'success',
                        'wholesale' => 'warning',
                        'bulk' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('change_reason')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'manual' => 'primary',
                        'automatic' => 'success',
                        'promotion' => 'warning',
                        'cost_change' => 'info',
                        'market_adjustment' => 'danger',
                        'seasonal' => 'secondary',
                        default => 'gray',
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
                        'regular' => 'Regular Price',
                        'sale' => 'Sale Price',
                        'wholesale' => 'Wholesale Price',
                        'bulk' => 'Bulk Price',
                    ]),
                Tables\Filters\SelectFilter::make('change_reason')
                    ->options([
                        'manual' => 'Manual Change',
                        'automatic' => 'Automatic Update',
                        'promotion' => 'Promotion',
                        'cost_change' => 'Cost Change',
                        'market_adjustment' => 'Market Adjustment',
                        'seasonal' => 'Seasonal Change',
                    ]),
                Tables\Filters\SelectFilter::make('variant_id')
                    ->relationship('variant', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('effective_date_range')
                    ->form([
                        Forms\Components\DatePicker::make('effective_from')
                            ->label('Effective From'),
                        Forms\Components\DatePicker::make('effective_until')
                            ->label('Effective Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['effective_from'],
                                fn ($query, $date) => $query->whereDate('effective_from', '>=', $date),
                            )
                            ->when(
                                $data['effective_until'],
                                fn ($query, $date) => $query->whereDate('effective_until', '<=', $date),
                            );
                    }),
                Tables\Filters\TernaryFilter::make('price_change')
                    ->label('Price Change')
                    ->placeholder('All changes')
                    ->trueLabel('Increases only')
                    ->falseLabel('Decreases only')
                    ->queries(
                        true: fn ($query) => $query->increases(),
                        false: fn ($query) => $query->decreases(),
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
            'index' => Pages\ListVariantPriceHistories::route('/'),
            'create' => Pages\CreateVariantPriceHistory::route('/create'),
            'view' => Pages\ViewVariantPriceHistory::route('/{record}'),
            'edit' => Pages\EditVariantPriceHistory::route('/{record}/edit'),
        ];
    }
}
