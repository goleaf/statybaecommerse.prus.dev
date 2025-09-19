<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\VariantImageResource\Pages;
use App\Models\VariantImage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Filters\TernaryFilter;
use UnitEnum;
use BackedEnum;

final class VariantImageResource extends Resource
{
    protected static ?string $model = VariantImage::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static string|UnitEnum|null $navigationGroup = 'Products';

    protected static ?int $navigationSort = 15;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('variant_id')
                    ->relationship('variant', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                Forms\Components\FileUpload::make('image_path')
                    ->image()
                    ->required()
                    ->directory('variant-images')
                    ->visibility('public'),

                Forms\Components\TextInput::make('alt_text')
                    ->label('Alt Text')
                    ->maxLength(255),

                Forms\Components\TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0),

                Forms\Components\Toggle::make('is_primary')
                    ->label('Primary Image')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Image')
                    ->size(60),

                Tables\Columns\TextColumn::make('variant.name')
                    ->label('Variant')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('alt_text')
                    ->label('Alt Text')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Sort Order')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_primary')
                    ->label('Primary')
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
                TernaryFilter::make('is_primary')
                    ->label('Primary Images Only'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
            'index' => Pages\ListVariantImages::route('/'),
            'create' => Pages\CreateVariantImage::route('/create'),
            'edit' => Pages\EditVariantImage::route('/{record}/edit'),
        ];
    }
}
