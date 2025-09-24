<?php

declare(strict_types=1);

namespace App\Filament\Resources\Sliders\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class SliderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.sliders.basic_information'))
                    ->description(__('admin.sliders.basic_information_description'))
                    ->components([
                        Grid::make(2)
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
                Section::make(__('admin.sliders.media'))
                    ->description(__('admin.sliders.media_description'))
                    ->components([
                        FileUpload::make('image')
                            ->label(__('admin.sliders.image'))
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->directory('sliders/images')
                            ->visibility('public')
                            ->maxSize(5120)  // 5MB
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
                Section::make(__('admin.sliders.call_to_action'))
                    ->description(__('admin.sliders.call_to_action_description'))
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextInput::make('button_text')
                                    ->label(__('admin.sliders.button_text'))
                                    ->maxLength(255)
                                    ->columnSpan(1),
                                TextInput::make('button_url')
                                    ->label(__('admin.sliders.button_url'))
                                    ->url()
                                    ->maxLength(255)
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->collapsible(),
                Section::make(__('admin.sliders.design'))
                    ->description(__('admin.sliders.design_description'))
                    ->components([
                        Grid::make(2)
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
                Section::make(__('admin.sliders.status'))
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
