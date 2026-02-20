<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make(__('admin.products.basic_information'))
                    ->description(__('admin.products.basic_information_description'))
                    ->schema([
                        Grid::make(3)
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
                                TextInput::make('sku')
                                    ->label(__('messages.sku'))
                                    ->required(),
                                TextInput::make('barcode')
                                    ->label(__('messages.barcode'))
                                    ->maxLength(100),
                                Select::make('brand_id')
                                    ->label(__('messages.brand'))
                                    ->relationship('brand', 'name')
                                    ->searchable()
                                    ->preload(),
                                Select::make('status')
                                    ->label(__('admin.products.status'))
                                    ->options([
                                        'draft'     => __('admin.products.status_draft'),
                                        'pending'   => __('admin.products.status_pending'),
                                        'published' => __('admin.products.status_published'),
                                        'archived'  => __('admin.products.status_archived'),
                                    ])
                                    ->default('draft')
                                    ->required(),
                                Toggle::make('is_featured')
                                    ->label(__('admin.products.is_featured'))
                                    ->default(false),
                                DateTimePicker::make('published_at')
                                    ->label(__('admin.products.published_at')),
                            ]),
                        RichEditor::make('description')
                            ->label(__('messages.description'))
                            ->columnSpanFull(),
                        RichEditor::make('detailed_description')
                            ->label(__('admin.products.detailed_description'))
                            ->columnSpanFull(),
                        Textarea::make('short_description')->label(__('admin.products.short_description'))
                            ->rows(3)
                            ->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                Select::make('collections')
                                    ->label(__('messages.collections'))
                                    ->relationship('collections', 'name')
                                    ->multiple()
                                    ->searchable()
                                    ->preload(),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make(__('admin.products.pricing'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('price')
                                    ->label(__('messages.price'))
                                    ->numeric()
                                    ->required()
                                    ->prefix('€'),
                                TextInput::make('cost_price')
                                    ->label(__('admin.products.cost_price'))
                                    ->numeric()
                                    ->prefix('€'),
                            ]),
                    ])
                    ->columnSpanFull(),
                Section::make(__('admin.products.inventory'))
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                Toggle::make('manage_stock')
                                    ->label(__('admin.products.manage_stock'))
                                    ->default(true),
                                Toggle::make('allow_backorder')
                                    ->label(__('admin.products.allow_backorder'))
                                    ->default(false),
                                TextInput::make('stock_quantity')
                                    ->label(__('admin.products.stock_quantity'))
                                    ->numeric()
                                    ->integer()
                                    ->default(0),
                                TextInput::make('low_stock_threshold')
                                    ->label(__('admin.products.low_stock_threshold'))
                                    ->numeric()
                                    ->integer()
                                    ->default(0),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make(__('admin.products.physical'))
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextInput::make('weight')
                                    ->label(__('admin.products.weight'))
                                    ->numeric()
                                    ->suffix(__('messages.unit_kg')),
                                TextInput::make('length')
                                    ->label(__('admin.products.length'))
                                    ->numeric()
                                    ->suffix(__('messages.unit_cm')),
                                TextInput::make('width')
                                    ->label(__('admin.products.width'))
                                    ->numeric()
                                    ->suffix(__('messages.unit_cm')),
                                TextInput::make('height')
                                    ->label(__('admin.products.height'))
                                    ->numeric()
                                    ->suffix(__('messages.unit_cm')),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('size')
                                    ->label(__('messages.size'))
                                    ->maxLength(255),
                                TextInput::make('size_type')
                                    ->label(__('admin.labels.size_type'))
                                    ->maxLength(255),
                                TextInput::make('color')
                                    ->label(__('translations.color'))
                                    ->maxLength(255),
                                TextInput::make('pack_size')
                                    ->label(__('attribute.pack_size'))
                                    ->maxLength(255),
                                TextInput::make('pack_size_type')
                                    ->label(__('admin.labels.pack_size_type'))
                                    ->maxLength(255),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
