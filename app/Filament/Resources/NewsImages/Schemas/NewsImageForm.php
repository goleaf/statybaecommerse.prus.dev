<?php

namespace App\Filament\Resources\NewsImages\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class NewsImageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('news_id')
                    ->relationship('news', 'id')
                    ->required(),
                TextInput::make('file_path')
                    ->required(),
                TextInput::make('alt_text'),
                Textarea::make('caption')
                    ->columnSpanFull(),
                Toggle::make('is_featured')
                    ->required(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('file_size')
                    ->numeric(),
                TextInput::make('mime_type'),
                Textarea::make('dimensions')
                    ->columnSpanFull(),
            ]);
    }
}
