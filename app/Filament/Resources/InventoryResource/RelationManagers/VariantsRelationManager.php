<?php

declare(strict_types=1);

namespace App\Filament\Resources\InventoryResource\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variant';

    protected static ?string $recordTitleAttribute = 'sku';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.navigation.product_variant');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('sku')
                    ->label(__('messages.sku'))
                    ->required(),
                TextInput::make('name')
                    ->label(__('messages.name')),
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
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }
}
