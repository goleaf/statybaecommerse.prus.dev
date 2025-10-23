<?php

declare(strict_types=1);

namespace App\Filament\Resources\MenuItems\Tables;

use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MenuItemsTable
{
    public static function configure(Table $table): Table
    {
        // Shared table configuration keeps the columns uniform between standalone and relation managers.
        return $table
            ->columns([
                TextColumn::make('menu.name')
                    ->searchable(),
                TextColumn::make('parent.id')
                    ->searchable(),
                TextColumn::make('label')
                    ->searchable(),
                TextColumn::make('url')
                    ->searchable(),
                TextColumn::make('route_name')
                    ->searchable(),
                TextColumn::make('icon')
                    ->searchable(),
                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_visible')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
