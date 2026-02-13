<?php

declare(strict_types=1);

namespace App\Filament\Resources\Companies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('industry')
                    ->label(__('messages.industry'))
                    ->badge()
                    ->icon(fn (\App\Enums\Industry $state): string => $state->icon())
                    ->color(fn (\App\Enums\Industry $state): string => $state->color())
                    ->searchable()
                    ->sortable(),
                TextColumn::make('size')
                    ->sortable(),
                TextColumn::make('address')
                    ->sortable()
                    ->label(__('messages.address'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email')
                    ->sortable()
                    ->label(__('messages.email'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('phone')
                    ->sortable()
                    ->label(__('messages.phone'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('website')
                    ->sortable()
                    ->label(__('messages.website'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('customers_count')
                    ->label(__('messages.customers'))
                    ->counts('customers')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->sortable()
                    ->label(__('messages.active'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('messages.active')),
                \Filament\Tables\Filters\SelectFilter::make('industry')
                    ->label(__('messages.industry'))
                    ->options(\App\Enums\Industry::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
