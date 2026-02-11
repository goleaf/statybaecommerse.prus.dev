<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductVariantResource\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductVariantInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.product_variants.general_info'))
                    ->schema([
                        TextEntry::make('product.name')
                            ->label(__('messages.product')),
                        TextEntry::make('sku')
                            ->label(__('messages.sku')),
                        TextEntry::make('name')
                            ->label(__('messages.name')),
                        TextEntry::make('barcode')
                            ->label(__('messages.barcode')),
                    ])->columns(2),

                Section::make(__('admin.product_variants.pricing'))
                    ->schema([
                        TextEntry::make('price')
                            ->label(__('messages.price'))
                            ->money('EUR'),
                        TextEntry::make('cost_price')
                            ->label(__('admin.products.cost_price'))
                            ->money('EUR'),
                        TextEntry::make('wholesale_price')
                            ->label(__('messages.wholesale_price'))
                            ->money('EUR'),
                        TextEntry::make('member_price')
                            ->label(__('messages.member_price'))
                            ->money('EUR'),
                        TextEntry::make('promotional_price')
                            ->label(__('messages.promotional_price'))
                            ->money('EUR'),
                    ])->columns(3),

                Section::make(__('admin.product_variants.inventory'))
                    ->schema([
                        TextEntry::make('stock_quantity')
                            ->label(__('admin.products.stock_quantity')),
                        TextEntry::make('low_stock_threshold')
                            ->label(__('admin.products.low_stock_threshold')),
                        IconEntry::make('track_inventory')
                            ->label(__('admin.products.track_stock'))
                            ->boolean(),
                        IconEntry::make('allow_backorder')
                            ->label(__('admin.products.allow_backorder'))
                            ->boolean(),
                    ])->columns(2),

                Section::make(__('admin.product_variants.dimensions'))
                    ->schema([
                        TextEntry::make('size')
                            ->label(__('messages.size')),
                        TextEntry::make('size_unit')
                            ->label(__('messages.size_unit')),
                        TextEntry::make('size_display')
                            ->label(__('messages.size_display')),
                        TextEntry::make('weight')
                            ->label(__('admin.products.weight'))
                            ->suffix(' kg'),
                    ])->columns(2),

                Section::make(__('admin.product_variants.status_features'))
                    ->schema([
                        IconEntry::make('is_enabled')
                            ->label(__('messages.is_enabled'))
                            ->boolean(),
                        IconEntry::make('is_default_variant')
                            ->label(__('messages.is_default_variant'))
                            ->boolean(),
                        IconEntry::make('is_featured')
                            ->label(__('messages.is_featured'))
                            ->boolean(),
                        IconEntry::make('is_new')
                            ->label(__('messages.is_new'))
                            ->boolean(),
                        IconEntry::make('is_bestseller')
                            ->label(__('messages.is_bestseller'))
                            ->boolean(),
                    ])->columns(5),

                Section::make(__('admin.product_variants.localization'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('variant_name_lt')
                                    ->label(__('admin.fields.name_lt')),
                                TextEntry::make('variant_name_en')
                                    ->label(__('admin.fields.name_en')),
                                TextEntry::make('description_lt')
                                    ->label(__('admin.fields.description_lt'))
                                    ->markdown(),
                                TextEntry::make('description_en')
                                    ->label(__('admin.fields.description_en'))
                                    ->markdown(),
                            ]),
                    ]),
            ]);
    }
}
