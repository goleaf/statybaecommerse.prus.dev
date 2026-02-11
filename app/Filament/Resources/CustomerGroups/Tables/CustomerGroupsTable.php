<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerGroups\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class CustomerGroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label(__('messages.code'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('messages.type'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('description')
                    ->label(__('messages.description'))
                    ->searchable()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                ColorColumn::make('color')
                    ->label(__('messages.color'))
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('icon')
                    ->label(__('messages.icon'))
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_active')
                    ->label(__('messages.active'))
                    ->sortable(),
                ToggleColumn::make('is_default')
                    ->label(__('messages.default'))
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('messages.sort'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('users_count')
                    ->label(__('messages.users'))
                    ->counts('users')
                    ->sortable(),
                TextColumn::make('customers_count')
                    ->label(__('messages.customers'))
                    ->counts('customers')
                    ->sortable(),
                TextColumn::make('discount_percentage')
                    ->label(__('messages.discount_percentage'))
                    ->numeric(2)
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('messages.created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
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
