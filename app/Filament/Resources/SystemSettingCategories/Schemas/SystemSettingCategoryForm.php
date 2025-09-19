<?php

namespace App\Filament\Resources\SystemSettingCategories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SystemSettingCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('icon'),
                TextInput::make('color')
                    ->default('primary'),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_active')
                    ->required(),
                Select::make('parent_id')
                    ->relationship('parent', 'name'),
                TextInput::make('template'),
                Textarea::make('metadata')
                    ->columnSpanFull(),
                Toggle::make('is_collapsible')
                    ->required(),
                Toggle::make('show_in_sidebar')
                    ->required(),
                TextInput::make('permission'),
                Textarea::make('tags')
                    ->columnSpanFull(),
            ]);
    }
}
