<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductRequestResource\Tables;

use App\Models\ProductRequest;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('product.name')
                    ->label(__('messages.product'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label(__('messages.user'))
                    ->searchable()
                    ->sortable()
                    ->placeholder(__('messages.guest')),
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email')
                    ->label(__('messages.email'))
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(__('messages.phone'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('requested_quantity')
                    ->label(__('messages.quantity'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('messages.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        ProductRequest::STATUS_PENDING     => 'warning',
                        ProductRequest::STATUS_IN_PROGRESS => 'info',
                        ProductRequest::STATUS_COMPLETED   => 'success',
                        ProductRequest::STATUS_CANCELLED   => 'danger',
                        default                            => 'secondary',
                    })
                    ->formatStateUsing(fn (string $state): string => __("translations.status_{$state}")),
                TextColumn::make('created_at')
                    ->label(__('messages.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('messages.status'))
                    ->options([
                        ProductRequest::STATUS_PENDING     => __('translations.status_pending'),
                        ProductRequest::STATUS_IN_PROGRESS => __('translations.status_in_progress'),
                        ProductRequest::STATUS_COMPLETED   => __('translations.status_completed'),
                        ProductRequest::STATUS_CANCELLED   => __('translations.status_cancelled'),
                    ]),
                SelectFilter::make('product')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
