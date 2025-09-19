<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\VariantAttributeValueResource\Pages;
use App\Models\VariantAttributeValue;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use UnitEnum;

final class VariantAttributeValueResource extends Resource
{
    protected static ?string $model = VariantAttributeValue::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static string|UnitEnum|null $navigationGroup = 'Products';

    protected static ?int $navigationSort = 18;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('variant_id')
                    ->relationship('variant', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('attribute_id')
                    ->relationship('attribute', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\TextInput::make('attribute_name')
                    ->label('Attribute Name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('attribute_value')
                    ->label('Attribute Value')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('attribute_value_display')
                    ->label('Display Value')
                    ->maxLength(255),

                Forms\Components\TextInput::make('attribute_value_lt')
                    ->label('Lithuanian Value')
                    ->maxLength(255),

                Forms\Components\TextInput::make('attribute_value_en')
                    ->label('English Value')
                    ->maxLength(255),

                Forms\Components\TextInput::make('attribute_value_slug')
                    ->label('Value Slug')
                    ->maxLength(255),

                Forms\Components\TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0),

                Forms\Components\Toggle::make('is_filterable')
                    ->label('Filterable')
                    ->default(true),

                Forms\Components\Toggle::make('is_searchable')
                    ->label('Searchable')
                    ->default(true),
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

                Tables\Columns\TextColumn::make('attribute.name')
                    ->label('Attribute')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('attribute_name')
                    ->label('Attribute Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('attribute_value')
                    ->label('Value')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('attribute_value_display')
                    ->label('Display Value')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('attribute_value_lt')
                    ->label('Lithuanian')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('attribute_value_en')
                    ->label('English')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Sort Order')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_filterable')
                    ->label('Filterable')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_searchable')
                    ->label('Searchable')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('variant_id')
                    ->relationship('variant', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('attribute_id')
                    ->relationship('attribute', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_filterable')
                    ->label('Filterable Only'),

                Tables\Filters\TernaryFilter::make('is_searchable')
                    ->label('Searchable Only'),
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
            ->defaultSort('sort_order');
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
            'index' => Pages\ListVariantAttributeValues::route('/'),
            'create' => Pages\CreateVariantAttributeValue::route('/create'),
            'edit' => Pages\EditVariantAttributeValue::route('/{record}/edit'),
        ];
    }
}
