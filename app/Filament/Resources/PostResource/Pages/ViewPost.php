<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostResource\Pages;


use Filament\Schemas\Schema;
use App\Filament\Resources\PostResource;
use App\Filament\Resources\UserResource;
use App\Models\Post;
use App\Models\PostApproval;
use Filament\Actions;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use LaraZeus\ListGroup\Entries\ListItem;
use LaraZeus\ListGroup\Infolists\ListEntry;

final class ViewPost extends ViewRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema   
    {
        return $schema->schema([
            ListEntry::make('postQuickLinks')
                ->heading(__('Quick links'))
                ->list()
                ->state(function (Post $record): array {
                    // Honour the active locale when building storefront and author shortcuts.
                    $locale = app()->getLocale();
                    $record->loadMissing(['user']);

                    $items = [
                        ListItem::make()
                            ->id('post-storefront-' . $record->getKey())
                            ->label(__('View on storefront'))
                            ->icon('heroicon-m-globe-alt')
                            ->color('primary')
                            ->url(route('frontend.posts.show', $record))
                            ->tooltip(__('Open the public article for :title', [
                                'title' => $record->getTranslatedTitle($locale),
                            ]))
                            ->toArray(),
                    ];

                    if ($record->user !== null) {
                        $items[] = ListItem::make()
                            ->id('post-author-' . $record->user->getKey())
                            ->label(__('posts.view_author_profile'))
                            ->icon('heroicon-m-user-circle')
                            ->color('info')
                            ->url(UserResource::getUrl('view', ['record' => $record->user]))
                            ->tooltip(__('posts.browse_by_author', [
                                'author' => $record->user->name ?? __('users.single'),
                            ]))
                            ->toArray();
                    }

                    return $items;
                }),
            ListEntry::make('postTags')
                ->heading(__('posts.tags'))
                ->list()
                ->state(function (Post $record): array {
                    // Split translated tag strings into individual localized quick filters.
                    $tags = collect(explode(',', (string) $record->getTranslatedTags(app()->getLocale())))
                        ->map(fn (string $tag): string => trim($tag))
                        ->filter()
                        ->values();

                    return $tags
                        ->map(function (string $tag, int $index) use ($record): array {
                            return ListItem::make()
                                ->id('post-tag-' . $record->getKey() . '-' . $index)
                                ->label($tag)
                                ->icon('heroicon-m-hashtag')
                                ->color('warning')
                                ->url(route('frontend.search.index', ['q' => $tag]))
                                ->tooltip(__('posts.search_tagged', ['tag' => $tag]))
                                ->toArray();
                        })
                        ->all();
                }),
            ListEntry::make('postApprovals')
                ->heading(__('Moderation history'))
                ->list()
                ->state(function (Post $record): array {
                    // Present moderation decisions with localized labels and timestamps.
                    $locale = app()->getLocale();
                    $record->loadMissing(['approvals.user']);

                    return $record->approvals
                        ->sortByDesc('decided_at')
                        ->map(function (PostApproval $approval) use ($record, $locale): array {
                            $decision = $approval->decision;
                            $color = match ($decision) {
                                'approved' => 'success',
                                'rejected', 'declined' => 'danger',
                                default => 'warning',
                            };

                            $user = $approval->user;
                            $userUrl = $user !== null
                                ? UserResource::getUrl('view', ['record' => $user])
                                : route('frontend.posts.show', $record);

                            return ListItem::make()
                                ->id('post-approval-' . $approval->getKey())
                                ->label(__('Decision: :decision', ['decision' => __($decision, [], $locale)]))
                                ->icon('heroicon-m-check-badge')
                                ->color($color)
                                ->url($userUrl)
                                ->tooltip(__('Decided on :date', [
                                    'date' => optional($approval->decided_at)->toDayDateTimeString(),
                                ]))
                                ->toArray();
                        })
                        ->all();
                }),
        ]);
    }
}