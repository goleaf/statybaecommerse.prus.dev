<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsComments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Schema;

class NewsCommentForm
{
    /**
     * Build the News Comment authoring schema so Filament renders a consistent admin form layout.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaGrid::make()
                    ->schema([
                        Select::make('news_id')
                            ->relationship('news', 'title')
                            ->label(__('admin.news_comments.news'))
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('parent_id')
                            ->relationship('parent', 'author_name')
                            ->label(__('admin.news_comments.parent_comment'))
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])
                    ->columns(2),
                SchemaGrid::make()
                    ->schema([
                        TextInput::make('author_name')
                            ->label(__('admin.news_comments.author_name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('author_email')
                            ->label(__('admin.news_comments.author_email'))
                            ->email()
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Textarea::make('content')
                    ->label(__('admin.news_comments.content'))
                    ->required()
                    ->rows(4)
                    ->columnSpanFull(),
                SchemaGrid::make()
                    ->schema([
                        Toggle::make('is_approved')
                            ->label(__('admin.news_comments.is_approved'))
                            ->default(false),
                        Toggle::make('is_visible')
                            ->label(__('admin.news_comments.is_visible'))
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
