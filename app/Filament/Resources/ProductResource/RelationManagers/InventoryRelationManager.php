<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class InventoryRelationManager extends RelationManager
{
    protected static string $relationship = 'inventories';

    protected static ?string $recordTitleAttribute = 'sku';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.inventory_management.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make([
                    'default' => 1,
                    'md'      => 3,
                ])
                    ->schema([
                        Select::make('warehouse_id')
                            ->label(__('messages.warehouse'))
                            ->relationship('warehouse', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        TextInput::make('sku')
                            ->label(__('messages.sku'))
                            ->maxLength(255),
                        TextInput::make('qty')
                            ->label(__('messages.quantity'))
                            ->numeric()
                            ->default(0)
                            ->required(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('warehouse.name')
                    ->label(__('messages.warehouse'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('sku')
                    ->label(__('messages.sku'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('qty')
                    ->label(__('messages.quantity'))
                    ->sortable(),
                TextColumn::make('available_quantity')
                    ->label(__('admin.inventory.available_quantity'))
                    ->getStateUsing(fn ($record) => $record->qty - ($record->meta['reserved'] ?? 0))
                    ->numeric()
                    ->sortable(),
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
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
