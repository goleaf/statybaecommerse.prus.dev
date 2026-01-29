<?php

declare(strict_types=1);

namespace App\Filament\Resources\UiTranslations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UiTranslationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label(__('messages.Key'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('locale')
                    ->label(__('messages.locale'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('group')
                    ->label(__('messages.Group'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('value')
                    ->label(__('messages.Value'))
                    ->limit(50)
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('locale')
                    ->label(__('messages.locale'))
                    ->options([
                        'lt' => 'Lithuanian',
                        'en' => 'English',
                        'de' => 'German',
                        'ru' => 'Russian',
                    ]),
                SelectFilter::make('group')
                    ->label(__('messages.Group')),
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
