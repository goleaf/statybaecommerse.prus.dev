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
                        TextEntry::make('product.name'),
                        TextEntry::make('sku')
                            ->label('SKU'),
                        TextEntry::make('name'),
                        TextEntry::make('barcode'),
                    ])->columns(2),

                Section::make(__('admin.product_variants.pricing'))
                    ->schema([
                        TextEntry::make('price')
                            ->money('EUR'),
                        TextEntry::make('compare_price')
                            ->money('EUR'),
                        TextEntry::make('cost_price')
                            ->money('EUR'),
                        TextEntry::make('wholesale_price')
                            ->money('EUR'),
                        TextEntry::make('member_price')
                            ->money('EUR'),
                        TextEntry::make('promotional_price')
                            ->money('EUR'),
                    ])->columns(3),

                Section::make(__('admin.product_variants.inventory'))
                    ->schema([
                        TextEntry::make('stock_quantity'),
                        TextEntry::make('low_stock_threshold'),
                        IconEntry::make('track_inventory')
                            ->boolean(),
                        IconEntry::make('allow_backorder')
                            ->boolean(),
                    ])->columns(2),

                Section::make(__('admin.product_variants.dimensions'))
                    ->schema([
                        TextEntry::make('size'),
                        TextEntry::make('size_unit'),
                        TextEntry::make('size_display'),
                        TextEntry::make('weight')
                            ->suffix(' kg'),
                    ])->columns(2),

                Section::make(__('admin.product_variants.status_features'))
                    ->schema([
                        IconEntry::make('is_enabled')
                            ->boolean(),
                        IconEntry::make('is_default_variant')
                            ->boolean(),
                        IconEntry::make('is_featured')
                            ->boolean(),
                        IconEntry::make('is_new')
                            ->boolean(),
                        IconEntry::make('is_bestseller')
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

                Section::make(__('admin.product_variants.seo'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('seo_title_lt')
                                    ->label(__('admin.fields.seo_title_lt')),
                                TextEntry::make('seo_title_en')
                                    ->label(__('admin.fields.seo_title_en')),
                                TextEntry::make('seo_description_lt')
                                    ->label(__('admin.fields.seo_description_lt')),
                                TextEntry::make('seo_description_en')
                                    ->label(__('admin.fields.seo_description_en')),
                            ]),
                    ])->collapsed(),
            ]);
    }
}
