<?php

declare(strict_types=1);

namespace App\Filament\Resources\CollectionResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CollectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->label(__('messages.image'))
                    ->collection('images')
                    ->circular(),
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label(__('messages.slug'))
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_visible')
                    ->label(__('messages.visible'))
                    ->boolean(),
                IconColumn::make('is_enabled')
                    ->label(__('messages.enabled'))
                    ->boolean(),
                IconColumn::make('is_automatic')
                    ->label(__('admin.collections.is_automatic'))
                    ->boolean(),
                TextColumn::make('products_count')
                    ->label(__('messages.products'))
                    ->counts('products')
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('messages.sort_order'))
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_visible')
                    ->label(__('messages.visible')),
                TernaryFilter::make('is_automatic')
                    ->label(__('admin.collections.is_automatic')),
            ])
            ->recordActions([
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
