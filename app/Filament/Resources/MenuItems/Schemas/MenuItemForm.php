<?php

namespace App\Filament\Resources\MenuItems\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MenuItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('menu_id')
                    ->relationship('menu', 'name')
                    ->required(),
                Select::make('parent_id')
                    ->relationship('parent', 'id'),
                TextInput::make('label')
                    ->required(),
                TextInput::make('url')
                    ->url(),
                TextInput::make('route_name'),
                Textarea::make('route_params')
                    ->columnSpanFull(),
                TextInput::make('icon'),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_visible')
                    ->required(),
            ]);
    }
}
