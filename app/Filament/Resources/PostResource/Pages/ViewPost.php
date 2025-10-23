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
        $post = $this->record->loadMissing([
            'user.posts' => fn ($query) => $query->where('status', 'published')->latest('published_at')->limit(5),
        ]);

        $locale = app()->getLocale();

        $resolveTranslation = static function (mixed $model, string $field) use ($locale): mixed {
            if (method_exists($model, 'getTranslation')) {
                $value = $model->getTranslation($field, $locale);
                if (filled($value)) {
                    return $value;
                }
            }

            if (method_exists($model, 'trans')) {
                $value = $model->trans($field, $locale);
                if (filled($value)) {
                    return $value;
                }
            }

            $translationsProperty = $field.'_translations';
            if (property_exists($model, $translationsProperty)) {
                $translations = $model->{$translationsProperty} ?? [];
                if (is_array($translations) && filled($translations[$locale] ?? null)) {
                    return $translations[$locale];
                }
            }

            return $model->{$field} ?? null;
        };

        $postTitle = $resolveTranslation($post, 'title') ?? $post->title;

        $quickLinks = [
            ListItem::make()
                ->id('storefront-post-'.$post->getKey())
                ->label(__('View blog post'))
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('primary')
                ->url(route('posts.show', $post))
                ->tooltip(__('Open the storefront page for :title', ['title' => $postTitle ?? __('this post')])),
        ];

        $authorPosts = $post->user?->posts ?? collect();

        $relatedItems = $authorPosts
            ->filter(fn ($related) => $related->getKey() !== $post->getKey())
            ->map(function ($related) use ($resolveTranslation) {
                $title = $resolveTranslation($related, 'title') ?? $related->title ?? __('Untitled post');

                return ListItem::make()
                    ->id('related-post-'.$related->getKey())
                    ->label($title)
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->url(route('posts.show', $related))
                    ->tooltip(__('Open :title on the storefront blog', ['title' => $title]));
            })
            ->values()
            ->all();

        return $infolist->schema([
            ListEntry::make('post_quick_links')
                ->heading(__('Quick links'))
                ->state(fn () => $quickLinks),
            ListEntry::make('author_posts')
                ->heading(__('More from this author'))
                ->list()
                ->state(fn () => $relatedItems),
        ]);
    }
}
