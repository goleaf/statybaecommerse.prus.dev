<?php

declare(strict_types=1);

namespace App\Filament\Resources\BrandResource\Schemas;

use App\Models\Brand;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BrandForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.brands.basic_information'))
                ->description(__('admin.brands.basic_information_description'))
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
                            Toggle::make('is_active')
                                ->label(__('admin.brands.is_active'))
                                ->default(true),
                            Toggle::make('is_premium')
                                ->label(__('admin.brands.is_premium'))
                                ->default(false),
                        ]),
                ])
                ->columnSpanFull(),
            Section::make(__('messages.media'))
                ->description(__('admin.brands.media_description'))
                ->schema([
                    SpatieMediaLibraryFileUpload::make('logo')
                        ->label(__('messages.image'))
                        ->collection('logo')
                        ->image()
                        ->columnSpanFull(),
                ]),
            Section::make(__('admin.brands.social_links'))
                ->schema([
                    Repeater::make('social_links')
                        ->schema([
                            Select::make('platform')
                                ->options(array_combine(Brand::SOCIAL_LINK_PLATFORMS, array_map('ucfirst', Brand::SOCIAL_LINK_PLATFORMS)))
                                ->required(),
                            TextInput::make('url')
                                ->url()
                                ->required(),
                        ])
                        ->columns(2),
                ])
                ->columnSpanFull(),
        ]);
    }
}
