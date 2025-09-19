<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\VariantStockHistoryResource\Pages;
use App\Models\VariantStockHistory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

final class VariantStockHistoryResource extends Resource
{
    protected static ?string $model = VariantStockHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static string|UnitEnum|null $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('variant_id')
                    ->relationship('variant', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\TextInput::make('old_quantity')
                    ->label('Old Quantity')
                    ->numeric()
                    ->minValue(0),

                Forms\Components\TextInput::make('new_quantity')
                    ->label('New Quantity')
                    ->numeric()
                    ->required()
                    ->minValue(0),

                Forms\Components\TextInput::make('quantity_change')
                    ->label('Quantity Change')
                    ->numeric()
                    ->disabled(),

                Forms\Components\Select::make('change_type')
                    ->options([
                        'increase' => 'Increase',
                        'decrease' => 'Decrease',
                        'adjustment' => 'Adjustment',
                        'reserve' => 'Reserve',
                        'unreserve' => 'Unreserve',
                    ])
                    ->required(),

                Forms\Components\Select::make('change_reason')
                    ->options([
                        'sale' => 'Sale',
                        'return' => 'Return',
                        'adjustment' => 'Stock Adjustment',
                        'reserve' => 'Reservation',
                        'unreserve' => 'Unreservation',
                        'damage' => 'Damage',
                        'theft' => 'Theft',
                        'expired' => 'Expired',
                        'manual' => 'Manual Adjustment',
                    ])
                    ->required(),

                Forms\Components\Select::make('changed_by')
                    ->relationship('changer', 'name')
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('reference_type')
                    ->options([
                        'order' => 'Order',
                        'return' => 'Return',
                        'adjustment' => 'Adjustment',
                        'reservation' => 'Reservation',
                    ]),

                Forms\Components\TextInput::make('reference_id')
                    ->label('Reference ID')
                    ->numeric(),
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

                Tables\Columns\TextColumn::make('old_quantity')
                    ->label('Old Quantity')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('new_quantity')
                    ->label('New Quantity')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('quantity_change')
                    ->label('Change')
                    ->formatStateUsing(function ($state) {
                        $sign = $state >= 0 ? '+' : '';
                        return $sign . $state;
                    })
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('change_type')
                    ->colors([
                        'success' => 'increase',
                        'danger' => 'decrease',
                        'warning' => 'adjustment',
                        'info' => 'reserve',
                        'secondary' => 'unreserve',
                    ]),

                Tables\Columns\BadgeColumn::make('change_reason')
                    ->colors([
                        'success' => 'sale',
                        'info' => 'return',
                        'warning' => 'adjustment',
                        'primary' => 'reserve',
                        'secondary' => 'unreserve',
                        'danger' => 'damage',
                        'danger' => 'theft',
                        'warning' => 'expired',
                        'gray' => 'manual',
                    ]),

                Tables\Columns\TextColumn::make('changer.name')
                    ->label('Changed By')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('reference_type')
                    ->label('Reference Type')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('reference_id')
                    ->label('Reference ID')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('change_type')
                    ->options([
                        'increase' => 'Increase',
                        'decrease' => 'Decrease',
                        'adjustment' => 'Adjustment',
                        'reserve' => 'Reserve',
                        'unreserve' => 'Unreserve',
                    ]),

                Tables\Filters\SelectFilter::make('change_reason')
                    ->options([
                        'sale' => 'Sale',
                        'return' => 'Return',
                        'adjustment' => 'Stock Adjustment',
                        'reserve' => 'Reservation',
                        'unreserve' => 'Unreservation',
                        'damage' => 'Damage',
                        'theft' => 'Theft',
                        'expired' => 'Expired',
                        'manual' => 'Manual Adjustment',
                    ]),

                Tables\Filters\SelectFilter::make('variant_id')
                    ->relationship('variant', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Created From'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Created Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn ($query, $date) => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn ($query, $date) => $query->whereDate('created_at', '<=', $date),
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
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListVariantStockHistories::route('/'),
            'create' => Pages\CreateVariantStockHistory::route('/create'),
            'edit' => Pages\EditVariantStockHistory::route('/{record}/edit'),
        ];
    }
}
