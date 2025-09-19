<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\VariantPriceHistoryResource\Pages;
use App\Models\VariantPriceHistory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

final class VariantPriceHistoryResource extends Resource
{
    protected static ?string $model = VariantPriceHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-euro';

    protected static string|UnitEnum|null $navigationGroup = 'Products';

    protected static ?int $navigationSort = 20;

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
                    ->step(0.0001),

                Forms\Components\TextInput::make('new_price')
                    ->label('New Price')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->step(0.0001),

                Forms\Components\Select::make('price_type')
                    ->options([
                        'regular' => 'Regular Price',
                        'sale' => 'Sale Price',
                        'wholesale' => 'Wholesale Price',
                        'bulk' => 'Bulk Price',
                    ])
                    ->required()
                    ->default('regular'),

                Forms\Components\Select::make('change_reason')
                    ->options([
                        'manual' => 'Manual Change',
                        'automatic' => 'Automatic Update',
                        'promotion' => 'Promotion',
                        'cost_change' => 'Cost Change',
                        'market_adjustment' => 'Market Adjustment',
                        'seasonal' => 'Seasonal Change',
                    ])
                    ->required()
                    ->default('manual'),

                Forms\Components\Select::make('changed_by')
                    ->relationship('changer', 'name')
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
                    ->searchable()
                    ->sortable(),

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
                            return $sign . '€' . number_format($change, 2) . ' (' . $sign . number_format($percentage, 1) . '%)';
                        }
                        return '-';
                    })
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('price_type')
                    ->colors([
                        'primary' => 'regular',
                        'success' => 'sale',
                        'warning' => 'wholesale',
                        'info' => 'bulk',
                    ]),

                Tables\Columns\BadgeColumn::make('change_reason')
                    ->colors([
                        'primary' => 'manual',
                        'success' => 'automatic',
                        'warning' => 'promotion',
                        'info' => 'cost_change',
                        'danger' => 'market_adjustment',
                        'secondary' => 'seasonal',
                    ]),

                Tables\Columns\TextColumn::make('changer.name')
                    ->label('Changed By')
                    ->searchable()
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

                Tables\Filters\Filter::make('effective_from')
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
                                fn ($query, $date) => $query->whereDate('effective_from', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('effective_from', 'desc');
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
            'edit' => Pages\EditVariantPriceHistory::route('/{record}/edit'),
        ];
    }
}
