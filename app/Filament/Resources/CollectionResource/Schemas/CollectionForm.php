<?php

declare(strict_types=1);

namespace App\Filament\Resources\CollectionResource\Schemas;

use App\Support\Filament\Forms\Components\SortOrderInput;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CollectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.brands.basic_information'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('messages.name'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                        TextInput::make('slug')
                            ->hidden(),
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
                        Grid::make(3)
                            ->schema([
                                Toggle::make('is_automatic')
                                    ->label(__('admin.collections.is_automatic'))
                                    ->default(false)
                                    ->live(),
                                SortOrderInput::make(),
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

                Section::make(__('admin.collections.automatic_skills'))
                    ->schema([
                        Repeater::make('rules')
                            ->relationship()
                            ->schema([
                                Select::make('column')
                                    ->label(__('admin.collections.rule_column'))
                                    ->options([
                                        'name'  => 'Product Name',
                                        'price' => 'Product Price',
                                        'sku'   => 'Product SKU',
                                    ])
                                    ->required(),
                                Select::make('operator')
                                    ->label(__('admin.collections.rule_operator'))
                                    ->options([
                                        '='        => 'Equals',
                                        '!='       => 'Not Equals',
                                        '>'        => 'Greater Than',
                                        '<'        => 'Less Than',
                                        'contains' => 'Contains',
                                    ])
                                    ->required(),
                                TextInput::make('value')
                                    ->label(__('admin.collections.rule_value'))
                                    ->required(),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (Get $get): bool => (bool) $get('is_automatic'))
                    ->columnSpanFull(),

                Section::make(__('messages.media'))
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('images')
                            ->label(__('messages.image'))
                            ->collection('images')
                            ->image()
                            ->columnSpanFull(),
                    ])->columnSpanFull(),
            ]);
    }
}
