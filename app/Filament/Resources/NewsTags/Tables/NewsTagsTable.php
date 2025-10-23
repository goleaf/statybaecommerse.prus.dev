<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsTags\Tables;

use App\Models\NewsTag;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Actions\BulkAction as TableBulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class NewsTagsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.news_tags.table.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('slug')
                    ->label(__('admin.news_tags.table.slug'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('description')
                    ->label(__('admin.news_tags.table.description'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (! is_string($state) || $state === '') {
                            return null;
                        }

                        return mb_strlen($state) > 50 ? $state : null;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                BadgeColumn::make('color')
                    ->label(__('admin.news_tags.table.color'))
                    ->colors([
                        'primary' => fn ($state): bool => $state === '#3B82F6',
                        'success' => fn ($state): bool => $state === '#10B981',
                        'warning' => fn ($state): bool => $state === '#F59E0B',
                        'danger'  => fn ($state): bool => $state === '#EF4444',
                    ])
                    ->formatStateUsing(fn ($state): string => $state ?? '#3B82F6')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_visible')
                    ->label(__('admin.news_tags.table.is_visible'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
                TextColumn::make('sort_order')
                    ->label(__('admin.news_tags.table.sort_order'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('news_count')
                    ->label(__('admin.news_tags.table.news_count'))
                    ->counts('news')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('created_at')
                    ->label(__('admin.news_tags.table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin.news_tags.table.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('is_visible')
                    ->label(__('admin.news_tags.filters.active'))
                    ->query(fn (Builder $query): Builder => $query->where('is_visible', true)),
                Filter::make('inactive')
                    ->label(__('admin.news_tags.filters.inactive'))
                    ->query(fn (Builder $query): Builder => $query->where('is_visible', false)),
                Filter::make('with_news')
                    ->label(__('admin.news_tags.filters.with_news'))
                    ->query(fn (Builder $query): Builder => $query->has('news')),
                Filter::make('without_news')
                    ->label(__('admin.news_tags.filters.without_news'))
                    ->query(fn (Builder $query): Builder => $query->doesntHave('news')),
                SelectFilter::make('color')
                    ->label(__('admin.news_tags.filters.color'))
                    ->options([
                        '#3B82F6' => 'Primary',
                        '#10B981' => 'Success',
                        '#F59E0B' => 'Warning',
                        '#EF4444' => 'Danger',
                    ]),
                Filter::make('recent')
                    ->label(__('admin.news_tags.filters.recent'))
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(7))),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('activate')
                    ->label(__('admin.news_tags.actions.activate'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (NewsTag $record): void {
                        $record->update(['is_visible' => true]);

                        Notification::make()
                            ->title(__('admin.news_tags.activated_successfully'))
                            ->success()
                            ->send();
                    }),
                Action::make('deactivate')
                    ->label(__('admin.news_tags.actions.deactivate'))
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->action(function (NewsTag $record): void {
                        $record->update(['is_visible' => false]);

                        Notification::make()
                            ->title(__('admin.news_tags.deactivated_successfully'))
                            ->success()
                            ->send();
                    }),
                Action::make('duplicate')
                    ->label(__('admin.news_tags.actions.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->action(function (NewsTag $record): void {
                        $newTag = $record->replicate();
                        $newTag->name = $record->name . ' (Copy)';
                        $newTag->slug = $record->slug . '-copy';
                        $newTag->save();

                        Notification::make()
                            ->title(__('admin.news_tags.duplicated_successfully'))
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    TableBulkAction::make('bulk_activate')
                        ->label(__('admin.news_tags.actions.bulk_activate'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each(function (NewsTag $record): void {
                                $record->update(['is_visible' => true]);
                            });

                            Notification::make()
                                ->title(__('admin.news_tags.bulk_activated_successfully'))
                                ->success()
                                ->send();
                        }),
                    TableBulkAction::make('bulk_deactivate')
                        ->label(__('admin.news_tags.actions.bulk_deactivate'))
                        ->icon('heroicon-o-x-circle')
                        ->color('gray')
                        ->action(function (Collection $records): void {
                            $records->each(function (NewsTag $record): void {
                                $record->update(['is_visible' => false]);
                            });

                            Notification::make()
                                ->title(__('admin.news_tags.bulk_deactivated_successfully'))
                                ->success()
                                ->send();
                        }),
                    TableBulkAction::make('bulk_duplicate')
                        ->label(__('admin.news_tags.actions.bulk_duplicate'))
                        ->icon('heroicon-o-document-duplicate')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            $records->each(function (NewsTag $record): void {
                                $newTag = $record->replicate();
                                $newTag->name = $record->name . ' (Copy)';
                                $newTag->slug = $record->slug . '-copy';
                                $newTag->save();
                            });

                            Notification::make()
                                ->title(__('admin.news_tags.bulk_duplicated_successfully'))
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('sort_order', 'asc');
    }
}
