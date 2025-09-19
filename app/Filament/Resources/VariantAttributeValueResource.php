<?php declare(strict_types=1);

namespace App\Filament\Resources;
use App\Filament\Resources\VariantAttributeValueResource\Pages;
use App\Models\VariantAttributeValue;
use Filament\Schemas\Schema;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use BackedEnum;
use App\Enums\NavigationGroup;
final class VariantAttributeValueResource extends Resource
{
    protected static ?string $model = VariantAttributeValue::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';
    // protected static $navigationGroup = NavigationGroup::System;
    protected static ?int $navigationSort = 18;
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('variant_id')
                    ->relationship('variant', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('attribute_id')
                    ->relationship('attribute', 'name')
                Forms\Components\TextInput::make('attribute_name')
                    ->label('Attribute Name')
                    ->maxLength(255),
                Forms\Components\TextInput::make('attribute_value')
                    ->label('Attribute Value')
                Forms\Components\TextInput::make('attribute_value_display')
                    ->label('Display Value')
                Forms\Components\TextInput::make('attribute_value_lt')
                    ->label('Lithuanian Value')
                Forms\Components\TextInput::make('attribute_value_en')
                    ->label('English Value')
                Forms\Components\TextInput::make('attribute_value_slug')
                    ->label('Value Slug')
                Forms\Components\TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('is_filterable')
                    ->label('Filterable')
                    ->default(true),
                Forms\Components\Toggle::make('is_searchable')
                    ->label('Searchable')
            ]);
    }
    public static function table(Table $table): Table
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('variant.name')
                    ->label('Variant')
                    ->sortable(),
                Tables\Columns\TextColumn::make('attribute.name')
                    ->label('Attribute')
                Tables\Columns\TextColumn::make('attribute_name')
                Tables\Columns\TextColumn::make('attribute_value')
                    ->label('Value')
                Tables\Columns\TextColumn::make('attribute_value_display')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('attribute_value_lt')
                    ->label('Lithuanian')
                Tables\Columns\TextColumn::make('attribute_value_en')
                    ->label('English')
                Tables\Columns\TextColumn::make('sort_order')
                Tables\Columns\IconColumn::make('is_filterable')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_searchable')
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('variant_id')
                Tables\Filters\SelectFilter::make('attribute_id')
                Tables\Filters\TernaryFilter::make('is_filterable')
                    ->label('Filterable Only'),
                Tables\Filters\TernaryFilter::make('is_searchable')
                    ->label('Searchable Only'),
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ->defaultSort('sort_order');
    public static function getRelations(): array
        return [
            //
        ];
    public static function getPages(): array
            'index' => Pages\ListVariantAttributeValues::route('/'),
            'create' => Pages\CreateVariantAttributeValue::route('/create'),
            'edit' => Pages\EditVariantAttributeValue::route('/{record}/edit'),
}
