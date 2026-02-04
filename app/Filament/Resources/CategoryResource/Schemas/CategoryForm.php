<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryResource\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.categories.basic_information'))
                ->description(__('admin.categories.basic_information_description'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('messages.name'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('slug')
                                ->label(__('messages.slug'))
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),
                        ]),
                    RichEditor::make('description')
                        ->label(__('messages.description'))
                        ->columnSpanFull(),
                    Grid::make(2)
                        ->schema([
                            Select::make('parent_id')
                                ->label(__('messages.category'))
                                ->relationship('parent', 'name')
                                ->searchable()
                                ->preload(),
                            Toggle::make('is_active')
                                ->label(__('admin.categories.is_active'))
                                ->default(true),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }
}
