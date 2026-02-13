<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductVariantResource\Schemas;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductVariantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.product_variants.general_info'))
                    ->schema([
                        Select::make('product_id')
                            ->label(__('messages.product'))
                            ->relationship('product', 'name')
                            ->required()
                            ->searchable(),
                        TextInput::make('sku')
                            ->label(__('messages.sku'))
                            ->required(),
                        TextInput::make('name')
                            ->label(__('messages.name'))
                            ->maxLength(255),
                        TextInput::make('barcode')
                            ->label(__('messages.barcode'))
                            ->maxLength(255),
                    ])->columns(2),

                Section::make(__('admin.product_variants.pricing'))
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
                        TextInput::make('wholesale_price')
                            ->label(__('messages.wholesale_price'))
                            ->numeric()
                            ->prefix('€'),
                        TextInput::make('member_price')
                            ->label(__('messages.member_price'))
                            ->numeric()
                            ->prefix('€'),
                        TextInput::make('promotional_price')
                            ->label(__('messages.promotional_price'))
                            ->numeric()
                            ->prefix('€'),
                    ])->columns(2),

                Section::make(__('admin.product_variants.inventory'))
                    ->schema([
                        TextInput::make('stock_quantity')
                            ->label(__('admin.products.stock_quantity'))
                            ->numeric()
                            ->default(0),
                        TextInput::make('low_stock_threshold')
                            ->label(__('admin.products.low_stock_threshold'))
                            ->numeric()
                            ->default(5),
                        Toggle::make('track_inventory')
                            ->label(__('admin.products.track_stock'))
                            ->default(true),
                        Toggle::make('allow_backorder')
                            ->label(__('admin.products.allow_backorder'))
                            ->default(false),
                    ])->columns(2),

                Section::make(__('admin.product_variants.dimensions'))
                    ->schema([
                        TextInput::make('size')
                            ->label(__('messages.size'))
                            ->maxLength(255),
                        TextInput::make('size_unit')
                            ->label(__('messages.size_unit'))
                            ->maxLength(255),
                        TextInput::make('size_display')
                            ->label(__('messages.size_display'))
                            ->maxLength(255),
                        TextInput::make('weight')
                            ->label(__('admin.products.weight'))
                            ->numeric()
                            ->suffix('kg'),
                    ])->columns(2),

                Section::make(__('admin.product_variants.status_features'))
                    ->schema([
                        Radio::make('is_enabled')
                            ->label(__('messages.is_enabled'))
                            ->boolean()
                            ->default(true)
                            ->inline()
                            ->columnSpanFull(),
                        Radio::make('is_default_variant')
                            ->label(__('messages.is_default_variant'))
                            ->boolean()
                            ->default(false)
                            ->inline()
                            ->columnSpanFull(),
                        Radio::make('is_featured')
                            ->label(__('messages.is_featured'))
                            ->boolean()
                            ->default(false)
                            ->inline()
                            ->columnSpanFull(),
                        Radio::make('is_new')
                            ->label(__('messages.is_new'))
                            ->boolean()
                            ->default(false)
                            ->inline()
                            ->columnSpanFull(),
                        Radio::make('is_bestseller')
                            ->label(__('messages.is_bestseller'))
                            ->boolean()
                            ->default(false)
                            ->inline()
                            ->columnSpanFull(),
                    ])->columns(1),

                Section::make(__('admin.product_variants.localization'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('variant_name_lt')
                                    ->label(__('admin.fields.name_lt')),
                                TextInput::make('variant_name_en')
                                    ->label(__('admin.fields.name_en')),
                                Textarea::make('description_lt')
                                    ->label(__('admin.fields.description_lt')),
                                Textarea::make('description_en')
                                    ->label(__('admin.fields.description_en')),
                            ]),
                    ]),
            ]);
    }
}
