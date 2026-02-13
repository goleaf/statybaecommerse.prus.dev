<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingCategoryResource\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class SystemSettingCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.system_setting_categories.basic_information'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('messages.name'))
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                            TextInput::make('slug')
                                ->label(__('messages.slug'))
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),
                        ]),
                    RichEditor::make('description')
                        ->label(__('messages.description'))
                        ->columnSpanFull(),
                    Grid::make(3)
                        ->schema([
                            TextInput::make('icon')
                                ->label(__('messages.icon'))
                                ->maxLength(255),
                            ColorPicker::make('color')
                                ->label(__('messages.color')),
                            TextInput::make('sort_order')
                                ->label(__('messages.sort_order'))
                                ->numeric()
                                ->default(0),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Select::make('parent_id')
                                ->label(__('messages.parent'))
                                ->relationship('parent', 'name')
                                ->searchable()
                                ->preload(),
                            Toggle::make('is_active')
                                ->label(__('messages.is_active'))
                                ->default(true),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }
}
