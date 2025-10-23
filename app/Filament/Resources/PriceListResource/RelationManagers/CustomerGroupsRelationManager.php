<?php

declare(strict_types=1);

namespace App\Filament\Resources\PriceListResource\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

final class CustomerGroupsRelationManager extends BaseRelationManager
{
    protected static string $relationship = 'customerGroups';

    protected static ?string $title = 'Customer Groups';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('price_lists.customer_group'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label(__('price_lists.code'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('discount_percentage')
                    ->label(__('price_lists.discount_percentage'))
                    ->formatStateUsing(fn (?float $state): string => $state !== null ? number_format($state, 2) . '%' : '-')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_enabled')
                    ->label(__('price_lists.is_active'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('price_lists.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                AttachAction::make()->preloadRecordSelect(),
            ])
            ->actions([
                DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}
