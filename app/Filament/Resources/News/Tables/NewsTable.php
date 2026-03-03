<?php

declare(strict_types=1);

namespace App\Filament\Resources\News\Tables;

use App\Enums\ModerationState;
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

final class NewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(static fn (Builder $query): Builder => $query->withCount('images'))
            ->columns([
                TextColumn::make('title')
                    ->label(__('messages.title'))
                    ->searchable(query: static function (Builder $query, string $search): Builder {
                        $like = '%' . trim($search) . '%';

                        return $query->whereHas('translations', static function (Builder $translationQuery) use ($like): void {
                            $translationQuery
                                ->where('title', 'like', $like)
                                ->orWhere('summary', 'like', $like)
                                ->orWhere('content', 'like', $like);
                        });
                    })
                    ->wrap()
                    ->limit(60),
                TextColumn::make('moderation_state')
                    ->label(__('messages.moderation'))
                    ->badge()
                    ->formatStateUsing(static function ($state): string {
                        $value = $state instanceof ModerationState ? $state->value : (string) $state;

                        return match ($value) {
                            'draft'     => __('admin.news.state_draft'),
                            'review'    => __('admin.news.state_review'),
                            'published' => __('admin.news.state_published'),
                            default     => $value,
                        };
                    })
                    ->color(static function ($state): string {
                        $value = $state instanceof ModerationState ? $state->value : (string) $state;

                        return match ($value) {
                            'draft'     => 'warning',
                            'review'    => 'info',
                            'published' => 'success',
                            default     => 'gray',
                        };
                    }),
                IconColumn::make('is_visible')
                    ->label(__('messages.visible'))
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->label(__('messages.featured'))
                    ->boolean(),
                IconColumn::make('is_breaking')
                    ->label(__('admin.news.is_breaking'))
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('published_at')
                    ->label(__('admin.news.published_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('author_name')
                    ->label(__('admin.news.author_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('images_count')
                    ->label(__('admin.news.images_count'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('messages.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('moderation_state')
                    ->label(__('messages.moderation'))
                    ->options([
                        'draft'     => __('admin.news.state_draft'),
                        'review'    => __('admin.news.state_review'),
                        'published' => __('admin.news.state_published'),
                    ]),
                TernaryFilter::make('is_visible')
                    ->label(__('messages.visible')),
                TernaryFilter::make('is_featured')
                    ->label(__('messages.featured')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('published_at', 'desc');
    }
}
