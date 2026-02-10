<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('messages.items');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->label(__('messages.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('unit_price', Product::find($state)?->price ?? 0))
                    ->required(),
                Select::make('product_variant_id')
                    ->label(__('admin.navigation.product_variant'))
                    ->relationship('productVariant', 'sku')
                    ->searchable()
                    ->preload()
                    ->visible(fn ($get) => filled($get('product_id')))
                    ->options(fn ($get) => ProductVariant::where('product_id', $get('product_id'))->pluck('sku', 'id')),
                TextInput::make('quantity')
                    ->label(__('messages.quantity'))
                    ->numeric()
                    ->default(1)
                    ->required(),
                TextInput::make('unit_price')
                    ->label(__('messages.unit_price'))
                    ->numeric()
                    ->prefix('€')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                \Filament\Tables\Columns\ImageColumn::make('product.primaryImage.path')
                    ->label(__('messages.image'))
                    ->disk('public')
                    ->square(),
                TextColumn::make('product.name')
                    ->label(__('messages.product'))
                    ->description(fn ($record) => $record->product?->sku)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label(__('messages.quantity'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('unit_price')
                    ->label(__('messages.unit_price'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('total')
                    ->label(__('messages.total'))
                    ->money('EUR')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('messages.add_to_cart')),
            ])
            ->recordActions([
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
