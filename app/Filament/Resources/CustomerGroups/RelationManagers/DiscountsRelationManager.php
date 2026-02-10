<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerGroups\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DiscountsRelationManager extends RelationManager
{
    protected static string $relationship = 'discounts';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('messages.type'))
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('value')
                    ->label(__('messages.value'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->label(__('messages.start_date'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label(__('messages.end_date'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect(),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
