<?php

declare(strict_types=1);

namespace App\Filament\Resources\BrandResource\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProductVariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $recordTitleAttribute = 'sku';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.navigation.product_variants');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('sku')
                    ->label(__('messages.sku'))
                    ->required(),
                TextInput::make('name')
                    ->label(__('messages.name'))
                    ->maxLength(255),
                TextInput::make('price')
                    ->label(__('messages.price'))
                    ->numeric()
                    ->required(),
                Toggle::make('is_enabled')
                    ->label(__('messages.enabled'))
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sku')
                    ->label(__('messages.sku'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('product.name')
                    ->label(__('messages.product'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('price')
                    ->label(__('messages.price'))
                    ->sortable(),
                ToggleColumn::make('is_enabled')
                    ->label(__('messages.enabled')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Creation might be complex through a relationship, better to create via product
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
