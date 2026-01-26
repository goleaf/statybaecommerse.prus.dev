<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema; // Changed from Form
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->label(__('orders.fields.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('unit_price', \App\Models\Product::find($state)?->price ?? 0)),
                TextInput::make('quantity')
                    ->label(__('orders.fields.quantity'))
                    ->numeric()
                    ->default(1)
                    ->required(),
                TextInput::make('unit_price')
                    ->label(__('orders.fields.unit_price'))
                    ->numeric()
                    ->required(),
                TextInput::make('total')
                    ->label(__('orders.fields.total'))
                    ->numeric()
                    ->disabled() // Auto-calculated in model
                    ->dehydrated(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('orders.fields.product'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('sku')
                    ->label(__('orders.fields.sku'))
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label(__('orders.fields.quantity'))
                    ->sortable(),
                TextColumn::make('unit_price')
                    ->label(__('orders.fields.unit_price'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('total')
                    ->label(__('orders.fields.total'))
                    ->money('EUR')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()->label(__('orders.actions.add_item')),
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
