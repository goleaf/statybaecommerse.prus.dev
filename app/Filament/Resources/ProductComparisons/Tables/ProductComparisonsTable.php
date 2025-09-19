<?php

namespace App\Filament\Resources\ProductComparisons\Tables;

use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductComparisonsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product1.name')
                    ->label('admin.product_comparison.product1')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product2.name')
                    ->label('admin.product_comparison.product2')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('similarity_score')
                    ->label('admin.product_comparison.similarity_score')
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
                SelectFilter::make('product1_id')
                    ->label('admin.product_comparison.product1')
                    ->relationship('product1', 'name'),
                SelectFilter::make('product2_id')
                    ->label('admin.product_comparison.product2')
                    ->relationship('product2', 'name'),
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
