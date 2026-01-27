<?php

namespace App\Filament\Resources\CustomerGroups\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
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
                ColorColumn::make('color')
                    ->label(__('messages.Color'))
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('icon')
                    ->label(__('common.Icon'))
                    ->toggleable(isToggledHiddenByDefault: true),
                ToggleColumn::make('is_active')
                    ->label(__('messages.active'))
                    ->sortable(),
                ToggleColumn::make('is_default')
                    ->label(__('messages.default'))
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('messages.Sort'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('messages.created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
