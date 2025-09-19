<?php

namespace App\Filament\Resources\NewsComments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class NewsCommentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('news_id')
                    ->relationship('news', 'id')
                    ->required(),
                Select::make('parent_id')
                    ->relationship('parent', 'id'),
                TextInput::make('author_name')
                    ->required(),
                TextInput::make('author_email')
                    ->email()
                    ->required(),
                Textarea::make('content')
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('is_approved')
                    ->required(),
                Toggle::make('is_visible')
                    ->required(),
            ]);
    }
}
