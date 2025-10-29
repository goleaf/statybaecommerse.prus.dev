<?php

declare(strict_types=1);

namespace App\Filament\Resources\Sliders\Schemas;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;

final class SliderForm
{
    public static function configure(Schema $form): Schema
    {
        // Continue using the injected form instance so we respect Filament's fluent schema builder lifecycle.
        return $form
            ->schema([
                SchemaSection::make(__('admin.sliders.basic_information'))
                    ->description(__('admin.sliders.basic_information_description'))
                    ->components([
                        SchemaGrid::make(2)
                            ->components([
                                TextInput::make('title')
                                    ->label(__('admin.sliders.title'))
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(1),
                                TextInput::make('sort_order')
                                    ->label(__('admin.sliders.sort_order'))
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->columnSpan(1),
                            ]),
                        Textarea::make('description')
                            ->label(__('admin.sliders.description'))
                            ->maxLength(2000)
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                SchemaSection::make(__('admin.sliders.media'))
                    ->description(__('admin.sliders.media_description'))
                    ->components([
                        SpatieMediaLibraryFileUpload::make('image')
                            ->label(__('admin.sliders.image'))
                            ->collection('slider_images')
                            ->disk('public')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->maxSize(5120)  // 5MB limit documented for reviewers.
                            ->columnSpanFull(),
                        SpatieMediaLibraryFileUpload::make('background')
                            ->label(__('admin.sliders.background_image'))
                            ->collection('slider_backgrounds')
                            ->disk('public')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->maxSize(5120)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
                SchemaSection::make(__('admin.sliders.call_to_action'))
                    ->description(__('admin.sliders.call_to_action_description'))
                    ->components([
                        SchemaGrid::make(3)
                            ->components([
                                TextInput::make('button_text')
                                    ->label(__('admin.sliders.button_text'))
                                    ->maxLength(255)
                                    ->columnSpan(1),
                                TextInput::make('button_url')
                                    ->label(__('admin.sliders.button_url'))
                                    ->placeholder(__('admin.sliders.button_url_placeholder'))
                                    ->helperText(__('admin.sliders.button_url_helper'))
                                    ->maxLength(255)
                                    ->url()
                                    ->nullable()
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->collapsible(),
                SchemaSection::make(__('admin.sliders.design'))
                    ->description(__('admin.sliders.design_description'))
                    ->components([
                        SchemaGrid::make(2)
                            ->components([
                                TextInput::make('background_color')
                                    ->label(__('admin.sliders.background_color'))
                                    ->required()
                                    ->default('#ffffff')
                                    ->columnSpan(1),
                                TextInput::make('text_color')
                                    ->label(__('admin.sliders.text_color'))
                                    ->required()
                                    ->default('#000000')
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->collapsible(),
                SchemaSection::make(__('admin.sliders.settings'))
                    ->description(__('admin.sliders.settings_description'))
                    ->components([
                        SchemaGrid::make(3)
                            ->components([
                                Toggle::make('settings.autoplay')
                                    ->label(__('admin.sliders.settings_autoplay'))
                                    ->default(true)
                                    ->columnSpan(1),
                                TextInput::make('settings.interval')
                                    ->label(__('admin.sliders.settings_interval'))
                                    ->numeric()
                                    ->default(5000)
                                    ->minValue(1000)
                                    ->step(500)
                                    ->columnSpan(1),
                                Toggle::make('settings.show_indicators')
                                    ->label(__('admin.sliders.settings_show_indicators'))
                                    ->default(true)
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->collapsible(),
                SchemaSection::make(__('admin.sliders.status'))
                    ->description(__('admin.sliders.status_description'))
                    ->components([
                        Toggle::make('is_active')
                            ->label(__('admin.sliders.is_active'))
                            ->default(true)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}
