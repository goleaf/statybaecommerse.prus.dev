<?php

declare(strict_types=1);

namespace App\Filament\Resources\Brochures\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
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
                    ->description(static fn (mixed $state, mixed $record): ?string => filled($record?->description) ? (string) $record->description : null)
                    ->limit(80)
                    ->wrap()
                    ->sortable(),
                TextColumn::make('files_count')
                    ->label(__('admin.brochures.files_label'))
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                TextColumn::make('active_files_count')
                    ->label(__('admin.brochures.active_files_label'))
                    ->badge()
                    ->color('success')
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
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('messages.active')),
                SelectFilter::make('has_active_files')
                    ->label(__('admin.brochures.active_files_filter'))
                    ->options([
                        'yes' => __('admin.yes'),
                        'no'  => __('admin.no'),
                    ])
                    ->query(static function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if ($value === 'yes') {
                            return $query->whereHas('files', static fn (Builder $filesQuery): Builder => $filesQuery->where('is_active', true));
                        }

                        if ($value === 'no') {
                            return $query->whereDoesntHave('files', static fn (Builder $filesQuery): Builder => $filesQuery->where('is_active', true));
                        }

                        return $query;
                    }),
            ])
            ->recordActions([
                Action::make('open_frontend')
                    ->label(__('admin.brochures.open_frontend'))
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(static fn (mixed $record): string => route('localized.brochures.index'))
                    ->openUrlInNewTab(),
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
            ->modifyQueryUsing(static fn (Builder $query): Builder => $query
                ->withCount('files')
                ->withCount([
                    'files as active_files_count' => static fn (Builder $filesQuery): Builder => $filesQuery->where('is_active', true),
                ])
                ->orderBy('sort_order')
                ->orderBy('title'))
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }
}
