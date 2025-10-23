<?php

declare(strict_types=1);

namespace App\Filament\Resources\MenuItems\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;

class MenuItemForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('menu_id')
                    ->relationship('menu', 'name')
                    ->required()
                    ->searchable(),
                Select::make('parent_id')
                    ->relationship('parent', 'label')
                    ->searchable(),
                TextInput::make('label')
                    ->required()
                    ->maxLength(255),
                TextInput::make('url')
                    ->url()
                    ->maxLength(255),
                TextInput::make('route_name')
                    ->maxLength(255),
                Textarea::make('route_params')
                    ->columnSpanFull(),
                TextInput::make('icon')
                    ->maxLength(100),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_visible')
                    ->required(),
            ]);
    }
}
