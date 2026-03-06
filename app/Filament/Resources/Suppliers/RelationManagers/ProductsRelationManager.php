<?php

declare(strict_types=1);

namespace App\Filament\Resources\Suppliers\RelationManagers;

use App\Models\Product;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['brand', 'primaryImage']))
            ->columns([
                ImageColumn::make('main_image')
                    ->label(__('messages.image'))
                    ->disk('public')
                    ->getStateUsing(static fn (Product $record): ?string => $record->main_image)
                    ->circular(),
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('sku')
                    ->label(__('messages.sku'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('brand.name')
                    ->label(__('messages.brand'))
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('admin.products.status'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('price')
                    ->label(__('messages.price'))
                    ->money('EUR')
                    ->sortable(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect(),
            ])
            ->actions([
                DetachAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
