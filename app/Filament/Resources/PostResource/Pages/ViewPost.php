<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostResource\Pages;

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

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            ListEntry::make('postQuickLinks')
                ->heading(__('Quick links'))
                ->state(function (Post $record): array {
                    return [
                        ListItem::make()
                            ->id('post-storefront-' . $record->getKey())
                            ->label(__('View on storefront'))
                            ->icon('heroicon-m-globe-alt')
                            ->color('primary')
                            ->url(route('frontend.posts.show', $record))
                            ->tooltip(__('Open the public article for :title', [
                                'title' => $record->getTranslatedTitle(),
                            ]))
                            ->toArray(),
                    ];
                }),
            ListEntry::make('postApprovals')
                ->heading(__('Moderation history'))
                ->list()
                ->state(function (Post $record): array {
                    $record->loadMissing(['approvals.user']);

                    return $record->approvals
                        ->sortByDesc('decided_at')
                        ->map(function (PostApproval $approval) use ($record): array {
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
                                ->label(__('Decision: :decision', ['decision' => __($decision)]))
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
