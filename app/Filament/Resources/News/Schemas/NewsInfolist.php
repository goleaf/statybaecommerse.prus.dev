<?php

declare(strict_types=1);

namespace App\Filament\Resources\News\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class NewsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.news.publication'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('title')
                                    ->label(__('messages.title')),
                                TextEntry::make('slug')
                                    ->label(__('messages.slug')),
                                TextEntry::make('moderation_state')
                                    ->label(__('messages.moderation'))
                                    ->badge()
                                    ->formatStateUsing(static fn (?string $state): string => match ($state) {
                                        'draft'     => __('admin.news.state_draft'),
                                        'review'    => __('admin.news.state_review'),
                                        'published' => __('admin.news.state_published'),
                                        default     => (string) $state,
                                    }),
                                TextEntry::make('published_at')
                                    ->label(__('admin.news.published_at'))
                                    ->dateTime(),
                                TextEntry::make('submitted_for_review_at')
                                    ->label(__('admin.news.submitted_for_review_at'))
                                    ->dateTime(),
                                TextEntry::make('approved_at')
                                    ->label(__('admin.news.approved_at'))
                                    ->dateTime(),
                                TextEntry::make('author_name')
                                    ->label(__('admin.news.author_name')),
                                TextEntry::make('author_email')
                                    ->label(__('admin.news.author_email')),
                                TextEntry::make('view_count')
                                    ->label(__('admin.news.view_count'))
                                    ->numeric(),
                            ]),
                        Grid::make(3)
                            ->schema([
                                IconEntry::make('is_visible')
                                    ->label(__('messages.visible'))
                                    ->boolean(),
                                IconEntry::make('is_featured')
                                    ->label(__('messages.featured'))
                                    ->boolean(),
                                IconEntry::make('is_breaking')
                                    ->label(__('admin.news.is_breaking'))
                                    ->boolean(),
                            ]),
                    ]),
                Section::make(__('admin.news.translations'))
                    ->schema([
                        TextEntry::make('summary')
                            ->label(__('messages.summary'))
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('content')
                            ->label(__('messages.content'))
                            ->html()
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('seo_title')
                            ->label(__('admin.news.seo_title'))
                            ->placeholder('-'),
                        TextEntry::make('seo_description')
                            ->label(__('admin.news.seo_description'))
                            ->placeholder('-'),
                    ]),
                Section::make(__('admin.news.audit'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('createdBy.name')
                                    ->label(__('admin.news.created_by'))
                                    ->placeholder('-'),
                                TextEntry::make('updatedBy.name')
                                    ->label(__('admin.news.updated_by'))
                                    ->placeholder('-'),
                                TextEntry::make('created_at')
                                    ->label(__('messages.created_at'))
                                    ->dateTime(),
                                TextEntry::make('updated_at')
                                    ->label(__('messages.updated_at'))
                                    ->dateTime(),
                            ]),
                    ]),
            ]);
    }
}
