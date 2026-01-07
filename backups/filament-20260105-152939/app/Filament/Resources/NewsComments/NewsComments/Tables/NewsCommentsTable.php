<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsComments\Tables;

use App\Models\NewsComment;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class NewsCommentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('news.title')
                    ->label(__('admin.news_comments.news'))
                    ->getStateUsing(static function (NewsComment $record): ?string {
                        $news = $record->news;

                        if ($news === null) {
                            return null;
                        }

                        $news->loadMissing('translations');

                        $locale = app()->getLocale();
                        $fallbackLocale = config('app.locale');

                        $translation = $news->translations
                            ->firstWhere('locale', $locale)
                            ?? ($fallbackLocale ? $news->translations->firstWhere('locale', $fallbackLocale) : null);

                        if ($translation !== null && isset($translation->title) && $translation->title !== '') {
                            return $translation->title;
                        }

                        return data_get($news->getAttributes(), 'title');
                    })
                    ->searchable(query: static function (Builder $query, string $search): Builder {
                        return $query->whereHas('news', function (Builder $newsQuery) use ($search): void {
                            $newsQuery->where(function (Builder $innerQuery) use ($search): void {
                                $innerQuery
                                    ->where('title', 'like', "%{$search}%")
                                    ->orWhereHas('translations', static function (Builder $translations) use ($search): void {
                                        $translations->where('title', 'like', "%{$search}%");
                                    });
                            });
                        });
                    })
                    ->limit(50)
                    ->tooltip(static fn (TextColumn $column): ?string => self::tooltipFromState($column, 50)),
                TextColumn::make('author_name')
                    ->label(__('admin.news_comments.author_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('author_email')
                    ->label(__('admin.news_comments.author_email'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('content')
                    ->label(__('admin.news_comments.content'))
                    ->limit(60)
                    ->tooltip(static fn (TextColumn $column): ?string => self::tooltipFromState($column, 60))
                    ->toggleable(),
                IconColumn::make('is_approved')
                    ->label(__('admin.news_comments.is_approved'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_visible')
                    ->label(__('admin.news_comments.is_visible'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('parent.author_name')
                    ->label(__('admin.news_comments.parent_comment'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin.common.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('news_id')
                    ->label(__('admin.news_comments.news'))
                    ->relationship('news', 'title')
                    ->searchable(),
                SelectFilter::make('parent_id')
                    ->label(__('admin.news_comments.parent_comment'))
                    ->relationship('parent', 'author_name')
                    ->searchable(),
                TernaryFilter::make('is_approved')
                    ->label(__('admin.news_comments.is_approved'))
                    ->boolean(),
                TernaryFilter::make('is_visible')
                    ->label(__('admin.news_comments.is_visible'))
                    ->boolean(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                Action::make('toggle_approval')
                    ->label(fn (NewsComment $record): string => $record->is_approved
                        ? __('admin.news_comments.disapprove')
                        : __('admin.news_comments.approve'))
                    ->icon(fn (NewsComment $record): string => $record->is_approved
                        ? 'heroicon-o-x-mark'
                        : 'heroicon-o-check')
                    ->color(fn (NewsComment $record): string => $record->is_approved ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->action(function (NewsComment $record): void {
                        $record->forceFill([
                            'is_approved' => ! $record->is_approved,
                        ])->save();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('approve')
                        ->label(__('admin.news_comments.approve_selected'))
                        ->icon('heroicon-o-check')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_approved' => true]);
                        }),
                    BulkAction::make('disapprove')
                        ->label(__('admin.news_comments.disapprove_selected'))
                        ->icon('heroicon-o-x-mark')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_approved' => false]);
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    private static function tooltipFromState(TextColumn $column, ?int $limit): ?string
    {
        $state = $column->getState();

        if (! is_string($state) || $state === '') {
            return null;
        }

        return $limit !== null && strlen($state) > $limit ? $state : null;
    }
}
