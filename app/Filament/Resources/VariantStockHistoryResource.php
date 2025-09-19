<?php declare(strict_types=1);

namespace App\Filament\Resources;
use App\Filament\Resources\VariantStockHistoryResource\Pages;
use App\Models\VariantStockHistory;
use Filament\Forms\Form;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use UnitEnum;
use BackedEnum;
final class VariantStockHistoryResource extends Resource
{
    protected static ?string $model = VariantStockHistory::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';
    /*protected static string | UnitEnum | null $navigationGroup = NavigationGroup::Inventory;
    protected static ?int $navigationSort = 3;
    public static function form(Schema $schema): Schema
    {
        return $schema
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
                Forms\Components\TextInput::make('quantity_change')
                    ->label('Quantity Change')
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
                        'sale' => 'Sale',
                        'return' => 'Return',
                        'adjustment' => 'Stock Adjustment',
                        'reserve' => 'Reservation',
                        'unreserve' => 'Unreservation',
                        'damage' => 'Damage',
                        'theft' => 'Theft',
                        'expired' => 'Expired',
                        'manual' => 'Manual Adjustment',
                Forms\Components\Select::make('changed_by')
                    ->relationship('changer', 'name')
                Forms\Components\Select::make('reference_type')
                        'order' => 'Order',
                        'reservation' => 'Reservation',
                    ]),
                Forms\Components\TextInput::make('reference_id')
                    ->label('Reference ID')
                    ->numeric(),
            ]);
    }
    public static function table(Table $table): Table
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('variant.name')
                    ->label('Variant')
                    ->sortable(),
                Tables\Columns\TextColumn::make('old_quantity')
                Tables\Columns\TextColumn::make('new_quantity')
                Tables\Columns\TextColumn::make('quantity_change')
                    ->label('Change')
                    ->formatStateUsing(function ($state) {
                        $sign = $state >= 0 ? '+' : '';
                        return $sign . $state;
                    })
                    ->color(fn($state) => $state >= 0 ? 'success' : 'danger')
                Tables\Columns\BadgeColumn::make('change_type')
                    ->colors([
                        'success' => 'increase',
                        'danger' => 'decrease',
                        'warning' => 'adjustment',
                        'info' => 'reserve',
                        'secondary' => 'unreserve',
                Tables\Columns\BadgeColumn::make('change_reason')
                        'success' => 'sale',
                        'info' => 'return',
                        'primary' => 'reserve',
                        'danger' => 'damage',
                        'danger' => 'theft',
                        'warning' => 'expired',
                        'gray' => 'manual',
                Tables\Columns\TextColumn::make('changer.name')
                    ->label('Changed By')
                Tables\Columns\TextColumn::make('reference_type')
                    ->label('Reference Type')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('reference_id')
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('change_type')
                Tables\Filters\SelectFilter::make('change_reason')
                Tables\Filters\SelectFilter::make('variant_id')
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Created From'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Created Until'),
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn($query, $date) => $query->whereDate('created_at', '>=', $date),
                            )
                                $data['created_until'],
                                fn($query, $date) => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ->defaultSort('created_at', 'desc');
    public static function getRelations(): array
        return [
            //
        ];
    public static function getPages(): array
            'index' => Pages\ListVariantStockHistories::route('/'),
            'create' => Pages\CreateVariantStockHistory::route('/create'),
            'edit' => Pages\EditVariantStockHistory::route('/{record}/edit'),
}
