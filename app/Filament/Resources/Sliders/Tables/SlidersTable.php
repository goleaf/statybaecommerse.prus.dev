<?php

declare(strict_types=1);

namespace App\Filament\Resources\Sliders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class SlidersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label(__('admin.sliders.image'))
                    ->getStateUsing(function ($record): ?string {
                        return $record->getFirstMedia('slider_images')?->getUrl('thumb');
                    })
                    ->defaultImageUrl('/images/placeholder-slider.png')
                    ->size(60)
                    ->square(),
                TextColumn::make('title')
                    ->label(__('admin.sliders.title'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(30),
                TextColumn::make('description')
                    ->label(__('admin.sliders.description'))
                    ->limit(50)
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('button_text')
                    ->label(__('admin.sliders.button_text'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('button_url')
                    ->label(__('admin.sliders.button_url'))
                    ->searchable()
                    ->toggleable()
                    ->limit(30),
                TextColumn::make('sort_order')
                    ->label(__('admin.sliders.sort_order'))
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                IconColumn::make('is_active')
                    ->label(__('admin.sliders.is_active'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('created_at')
                    ->label(__('admin.sliders.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin.sliders.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('admin.sliders.is_active'))
                    ->placeholder(__('admin.sliders.all_sliders'))
                    ->trueLabel(__('admin.sliders.active_sliders'))
                    ->falseLabel(__('admin.sliders.inactive_sliders')),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc')
            ->paginated([10, 25, 50]);
    }
}
