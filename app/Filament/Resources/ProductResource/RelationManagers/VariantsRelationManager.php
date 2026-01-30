<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $recordTitleAttribute = 'sku';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('messages.variants');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('sku')
                    ->label(__('messages.sku'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('name')
                    ->label(__('messages.name'))
                    ->maxLength(255),
                TextInput::make('price')
                    ->label(__('messages.price'))
                    ->numeric()
                    ->required(),
                TextInput::make('stock_quantity')
                    ->label(__('messages.stock_quantity'))
                    ->numeric()
                    ->default(0),
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
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('price')
                    ->label(__('messages.price'))
                    ->sortable(),
                TextColumn::make('stock_quantity')
                    ->label(__('messages.stock_quantity'))
                    ->sortable(),
                ToggleColumn::make('is_enabled')
                    ->label(__('messages.enabled')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
