<?php

declare(strict_types=1);

namespace App\Filament\Resources\Partners\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

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
            ->recordTitleAttribute('order_number')
            ->columns([
                TextColumn::make('order_number')
                    ->label(__('messages.order_number'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('messages.status'))
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label(__('messages.total'))
                    ->money()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('messages.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('messages.status'))
                    ->options([
                        'pending'    => __('messages.pending'),
                        'processing' => __('messages.processing'),
                        'completed'  => __('messages.completed'),
                        'cancelled'  => __('messages.cancelled'),
                    ]),
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
