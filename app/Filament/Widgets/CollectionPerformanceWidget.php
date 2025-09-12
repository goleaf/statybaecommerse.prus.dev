<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Collection;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

final class CollectionPerformanceWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 3;

    protected static ?string $heading = 'Collection Performance Analytics';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Collection::query()
                    ->withCount('products')
                    ->withSum('products as total_views', 'views_count')
                    ->withSum('products as total_sales', 'sales_count')
                    ->withAvg('products as avg_rating', 'rating')
                    ->orderBy('total_sales', 'desc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label(__('admin.collections.fields.image'))
                    ->getStateUsing(fn (Collection $record) => $record->getFirstMediaUrl('images', 'thumb'))
                    ->defaultImageUrl(asset('images/placeholder-collection.png'))
                    ->circular()
                    ->size(40),
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.collections.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->wrap(),
                Tables\Columns\TextColumn::make('products_count')
                    ->label(__('admin.collections.fields.products_count'))
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('total_views')
                    ->label(__('admin.collections.analytics.total_views'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->formatStateUsing(fn ($state) => number_format($state ?? 0)),
                Tables\Columns\TextColumn::make('total_sales')
                    ->label(__('admin.collections.analytics.total_sales'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->formatStateUsing(fn ($state) => number_format($state ?? 0)),
                Tables\Columns\TextColumn::make('avg_rating')
                    ->label(__('admin.collections.analytics.avg_rating'))
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 1).' ⭐' : 'N/A'),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label(__('admin.collections.fields.is_visible'))
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.collections.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label(__('admin.collections.actions.view')),
                Tables\Actions\EditAction::make()
                    ->label(__('admin.collections.actions.edit')),
            ])
            ->defaultSort('total_sales', 'desc');
    }
}
