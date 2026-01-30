<?php

declare(strict_types=1);

namespace App\Filament\Resources\BrandResource\Tables;

use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class BrandsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('logo')
                    ->label(__('messages.image'))
                    ->collection('logo')
                    ->circular(),
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label(__('messages.slug'))
                    ->searchable()
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label(__('admin.brands.is_active')),
                TextColumn::make('products_count')
                    ->label(__('admin.brands.products_count'))
                    ->counts('products')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.brands.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name');
    }
}
