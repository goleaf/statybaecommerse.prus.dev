<?php

declare(strict_types=1);

namespace App\Filament\Resources\NewsTags\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Str;

class NewsTagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SchemaSection::make(__('admin.news_tags.form.sections.basic_information'))
                    ->columns(1)
                    ->components([
                        TextInput::make('name')
                            ->label(__('admin.news_tags.form.fields.name'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')
                            ->label(__('admin.news_tags.form.fields.slug'))
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label(__('admin.news_tags.form.fields.description'))
                            ->rows(3)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ]),
                SchemaSection::make(__('admin.news_tags.form.sections.translations'))
                    ->columns(1)
                    ->collapsible()
                    ->components([
                        Repeater::make('translations')
                            ->label(__('admin.news_tags.form.fields.translations'))
                            ->relationship('translations')
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['locale'] ?? null)
                            ->schema([
                                Select::make('locale')
                                    ->label(__('admin.news_tags.form.fields.locale'))
                                    ->options([
                                        'lt' => 'Lietuvių',
                                        'en' => 'English',
                                    ])
                                    ->required(),
                                TextInput::make('name')
                                    ->label(__('admin.news_tags.form.fields.name'))
                                    ->required(),
                                TextInput::make('slug')
                                    ->label(__('admin.news_tags.form.fields.slug'))
                                    ->required(),
                                Textarea::make('description')
                                    ->label(__('admin.news_tags.form.fields.description'))
                                    ->rows(2)
                                    ->columnSpan(2),
                            ]),
                    ]),
                SchemaSection::make(__('admin.news_tags.form.sections.metadata'))
                    ->columns(3)
                    ->components([
                        Toggle::make('is_visible')
                            ->label(__('admin.news_tags.form.fields.is_visible'))
                            ->required()
                            ->default(true),
                        TextInput::make('sort_order')
                            ->label(__('admin.news_tags.form.fields.sort_order'))
                            ->numeric()
                            ->default(0),
                        ColorPicker::make('color')
                            ->label(__('admin.news_tags.form.fields.color'))
                            ->default('#3B82F6'),
                    ]),
            ]);
    }
}
