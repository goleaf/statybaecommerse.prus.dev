<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\RelationManagers\Support\BaseRelationManager;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WishlistRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'wishlists';

    protected static ?string $title = 'Wishlist';

    protected static ?string $modelLabel = 'Wishlist Item';

    protected static ?string $pluralModelLabel = 'Wishlist Items';

    public function table(Table $table): Table
    {
        // Configure the relation manager table to satisfy Filament v4's return type requirements.
        return $table
            ->columns([
                ImageColumn::make('product.main_image')
                    ->label(__('wishlist.fields.image'))
                    ->circular()
                    ->size(50),
                TextColumn::make('product.name')
                    ->label(__('wishlist.fields.product'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('product.sku')
                    ->label(__('wishlist.fields.sku'))
                    ->searchable()
                    ->copyable(),
                TextColumn::make('product.price')
                    ->label(__('wishlist.fields.price'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('wishlist.fields.added_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}