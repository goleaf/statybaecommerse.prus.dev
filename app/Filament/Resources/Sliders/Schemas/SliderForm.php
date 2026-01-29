<?php

declare(strict_types=1);

namespace App\Filament\Resources\Sliders\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SliderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('translations.basic_information'))
                    ->schema([
                        TextInput::make('title')
                            ->label(__('messages.title'))
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label(__('messages.description'))
                            ->maxLength(1000),
                    ]),

                Section::make(__('translations.media'))
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('image')
                            ->label(__('translations.slide_image'))
                            ->collection('slider_images')
                            ->image()
                            ->required(),
                        SpatieMediaLibraryFileUpload::make('background_image')
                            ->label(__('translations.slider_backgrounds'))
                            ->collection('slider_backgrounds')
                            ->image(),
                    ])->columns(2),

                Section::make(__('translations.design'))
                    ->schema([
                        ColorPicker::make('background_color')
                            ->label(__('translations.background_color')),
                        ColorPicker::make('text_color')
                            ->label(__('translations.text_color')),
                        TextInput::make('button_text')
                            ->label(__('translations.button_text'))
                            ->maxLength(255),
                        TextInput::make('button_url')
                            ->label(__('translations.button_url'))
                            ->url()
                            ->maxLength(255),
                    ])->columns(2),

                Section::make(__('translations.settings'))
                    ->schema([
                        TextInput::make('sort_order')
                            ->label(__('translations.sort_order'))
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label(__('translations.is_active'))
                            ->default(true)
                            ->required(),
                        KeyValue::make('settings')
                            ->label(__('translations.animation_settings')),
                    ])->columns(2),
            ]);
    }
}
