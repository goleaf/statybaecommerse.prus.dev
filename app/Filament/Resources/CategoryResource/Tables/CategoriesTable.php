<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryResource\Tables;

use App\Filament\Resources\CategoryResource;
use App\Models\Category;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(fn (Category $record): string => CategoryResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label(__('messages.slug'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('parent.name')
                    ->label(__('messages.category'))
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label(__('admin.categories.is_active')),
                TextColumn::make('products_count')
                    ->label(__('admin.categories.products_count'))
                    ->counts('products')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.categories.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}
