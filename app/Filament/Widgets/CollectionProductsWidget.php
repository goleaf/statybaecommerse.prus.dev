<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Collection;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

final class CollectionProductsWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    protected static ?string $heading = 'Collections with Most Products';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Collection::query()
                    ->withCount('products')
                    ->orderBy('products_count', 'desc')
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
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('admin.collections.fields.slug'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage(__('admin.copied'))
                    ->weight('mono'),
                Tables\Columns\IconColumn::make('is_visible')
                    ->label(__('admin.collections.fields.is_visible'))
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\IconColumn::make('is_automatic')
                    ->label(__('admin.collections.fields.is_automatic'))
                    ->boolean()
                    ->trueIcon('heroicon-o-cog-6-tooth')
                    ->falseIcon('heroicon-o-hand-raised')
                    ->trueColor('info')
                    ->falseColor('gray'),
                Tables\Columns\TextColumn::make('products_count')
                    ->label(__('admin.collections.fields.products_count'))
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('admin.collections.fields.sort_order'))
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ->defaultSort('products_count', 'desc');
    }
}
