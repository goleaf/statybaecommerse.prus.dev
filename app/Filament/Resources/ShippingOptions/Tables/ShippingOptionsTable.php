<?php

namespace App\Filament\Resources\ShippingOptions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShippingOptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('carrier_name')
                    ->searchable(),
                TextColumn::make('service_type')
                    ->searchable(),
                TextColumn::make('price')
                    ->money()
                    ->sortable(),
                TextColumn::make('currency_code')
                    ->searchable(),
                TextColumn::make('zone.name')
                    ->searchable(),
                IconColumn::make('is_enabled')
                    ->boolean(),
                IconColumn::make('is_default')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('min_weight')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_weight')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('min_order_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_order_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('estimated_days_min')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('estimated_days_max')
                    ->numeric()
                    ->sortable(),
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
