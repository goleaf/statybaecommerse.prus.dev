<?php

declare(strict_types=1);

namespace App\Filament\Resources\Brochures\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class BrochuresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('messages.title'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('files_count')
                    ->label(__('admin.brochures.files_label'))
                    ->counts('files')
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('messages.sort_order'))
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('messages.active'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('messages.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading(__('admin.brochures.delete_heading'))
                    ->modalDescription(__('admin.brochures.delete_warning')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.brochures.delete_heading'))
                        ->modalDescription(__('admin.brochures.delete_warning')),
                ]),
            ])
            ->modifyQueryUsing(static fn (Builder $query): Builder => $query->orderBy('sort_order')->orderBy('title'))
            ->defaultSort('sort_order');
    }
}
