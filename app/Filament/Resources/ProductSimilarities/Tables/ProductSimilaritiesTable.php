<?php

namespace App\Filament\Resources\ProductSimilarities\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductSimilaritiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('admin.product_similarity.product1')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('similarProduct.name')
                    ->label('admin.product_similarity.product2')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('similarity_score')
                    ->label('admin.product_similarity.similarity_score')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('admin.common.created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('admin.common.updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->label('admin.product_similarity.product1')
                    ->relationship('product', 'name'),
                SelectFilter::make('similar_product_id')
                    ->label('admin.product_similarity.product2')
                    ->relationship('similarProduct', 'name'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
