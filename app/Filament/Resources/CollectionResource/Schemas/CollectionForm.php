<?php

declare(strict_types=1);

namespace App\Filament\Resources\CollectionResource\Schemas;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CollectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.brands.basic_information'))
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
                                    ->maxLength(255),
                            ]),
                        RichEditor::make('description')
                            ->label(__('messages.description'))
                            ->columnSpanFull(),
                        Grid::make(3)
                            ->schema([
                                Toggle::make('is_visible')
                                    ->label(__('messages.visible'))
                                    ->default(true),
                                Toggle::make('is_enabled')
                                    ->label(__('messages.enabled'))
                                    ->default(true),
                                Toggle::make('is_active')
                                    ->label(__('messages.active'))
                                    ->default(true),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_automatic')
                                    ->label(__('admin.collections.is_automatic'))
                                    ->default(false),
                                TextInput::make('sort_order')
                                    ->label(__('messages.sort_order'))
                                    ->numeric()
                                    ->default(0),
                            ]),
                        Grid::make(3)
                            ->schema([
                                Select::make('display_type')
                                    ->label(__('admin.collections.display_type'))
                                    ->options([
                                        'grid' => 'Grid',
                                        'list' => 'List',
                                    ])
                                    ->default('grid'),
                                TextInput::make('products_per_page')
                                    ->label(__('admin.collections.products_per_page'))
                                    ->numeric()
                                    ->default(12),
                                Toggle::make('show_filters')
                                    ->label(__('admin.collections.show_filters'))
                                    ->default(true),
                            ]),
                    ])->columnSpanFull(),

                Section::make(__('messages.media'))
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('images')
                            ->label(__('messages.image'))
                            ->collection('images')
                            ->image()
                            ->columnSpanFull(),
                    ])->columnSpanFull(),

                Section::make(__('admin.products.seo'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('seo_title')
                                    ->label(__('admin.products.seo_title'))
                                    ->maxLength(255),
                                Textarea::make('seo_description')
                                    ->label(__('admin.products.seo_description'))
                                    ->rows(3),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}