<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\Tables;

use App\Enums\ExportType;
use App\Filament\Actions\RequestExportBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label(__('messages.order_number'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('user.name')
                    ->label(__('messages.customer'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('messages.status'))
                    ->badge()
                    ->searchable(),
                TextColumn::make('total')
                    ->label(__('messages.total'))
                    ->money(fn ($record) => $record->currency)
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->label(__('messages.payment_status'))
                    ->formatStateUsing(fn ($state) => $state instanceof \App\Enums\PaymentStatus ? $state->getLabel() : $state)
                    ->badge()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(__('messages.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label(__('messages.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('shipping_option.name')
                    ->label(__('messages.shipping'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tracking_number')
                    ->label(__('messages.tracking_number'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([ // Changed to bulkActions as it makes more sense for BulkActionGroup
                RequestExportBulkAction::make(ExportType::ORDERS),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
