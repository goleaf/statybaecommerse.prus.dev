<?php

declare(strict_types=1);

namespace App\Filament\Resources\RecommendationCaches\Tables;

use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class RecommendationCachesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cache_key')
                    ->label(__('admin.recommendation_caches.cache_key'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (! is_string($state)) {
                            return null;
                        }

                        return strlen($state) > 30 ? $state : null;
                    }),
                TextColumn::make('block.name')
                    ->label(__('admin.recommendation_caches.block'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label(__('admin.recommendation_caches.user'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label(__('admin.recommendation_caches.product'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (! is_string($state)) {
                            return null;
                        }

                        return strlen($state) > 30 ? $state : null;
                    }),
                TextColumn::make('context_type')
                    ->label(__('admin.recommendation_caches.context_type'))
                    ->badge()
                    ->color('info'),
                TextColumn::make('hit_count')
                    ->label(__('admin.recommendation_caches.hit_count'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->label(__('admin.recommendation_caches.expires_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.recommendation_caches.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('block_id')
                    ->label(__('admin.recommendation_caches.block'))
                    ->relationship('block', 'name')
                    ->searchable(),
                SelectFilter::make('user_id')
                    ->label(__('admin.recommendation_caches.user'))
                    ->relationship('user', 'name')
                    ->searchable(),
                SelectFilter::make('product_id')
                    ->label(__('admin.recommendation_caches.product'))
                    ->relationship('product', 'name')
                    ->searchable(),
                SelectFilter::make('context_type')
                    ->label(__('admin.recommendation_caches.context_type'))
                    ->options([
                        'homepage' => __('admin.recommendation_caches.context_types.homepage'),
                        'product'  => __('admin.recommendation_caches.context_types.product'),
                        'category' => __('admin.recommendation_caches.context_types.category'),
                        'cart'     => __('admin.recommendation_caches.context_types.cart'),
                        'checkout' => __('admin.recommendation_caches.context_types.checkout'),
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('expires_at', 'desc');
    }
}
