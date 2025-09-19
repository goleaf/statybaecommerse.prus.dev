<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\VariantCombinationResource\Pages;
use App\Models\VariantCombination;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

final class VariantCombinationResource extends Resource
{
    protected static ?string $model = VariantCombination::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string|UnitEnum|null $navigationGroup = 'Products';

    protected static ?int $navigationSort = 19;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\KeyValue::make('attribute_combinations')
                    ->keyLabel('Attribute')
                    ->valueLabel('Value')
                    ->columnSpanFull(),

                Forms\Components\Toggle::make('is_available')
                    ->label('Available')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('attribute_combinations')
                    ->label('Combinations')
                    ->formatStateUsing(function ($state) {
                        if (is_array($state)) {
                            return collect($state)->map(function ($value, $key) {
                                return $key . ': ' . $value;
                            })->join(', ');
                        }
                        return $state;
                    })
                    ->limit(50),

                Tables\Columns\IconColumn::make('is_available')
                    ->label('Available')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_available')
                    ->label('Available Only'),
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
            'index' => Pages\ListVariantCombinations::route('/'),
            'create' => Pages\CreateVariantCombination::route('/create'),
            'edit' => Pages\EditVariantCombination::route('/{record}/edit'),
        ];
    }
}
