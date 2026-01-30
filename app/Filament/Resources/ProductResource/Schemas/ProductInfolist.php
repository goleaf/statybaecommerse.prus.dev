<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.products.basic_information'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextEntry::make('name')
                                ->label(__('messages.name')),
                            TextEntry::make('slug')
                                ->label(__('messages.slug')),
                            TextEntry::make('sku')
                                ->label(__('messages.sku')),
                            TextEntry::make('barcode')
                                ->label(__('messages.barcode')),
                            TextEntry::make('brand.name')
                                ->label(__('messages.brand')),
                            TextEntry::make('status')
                                ->label(__('admin.products.status'))
                                ->badge(),
                            IconEntry::make('is_visible')
                                ->label(__('admin.products.is_visible'))
                                ->boolean(),
                            IconEntry::make('is_featured')
                                ->label(__('admin.products.is_featured'))
                                ->boolean(),
                            TextEntry::make('published_at')
                                ->label(__('admin.products.published_at'))
                                ->dateTime(),
                        ]),
                    TextEntry::make('description')
                        ->label(__('messages.description'))
                        ->html()
                        ->columnSpanFull(),
                    TextEntry::make('short_description')
                        ->label(__('admin.products.short_description'))
                        ->columnSpanFull(),
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('categories.name')
                                ->label(__('messages.categories'))
                                ->badge(),
                            TextEntry::make('collections.name')
                                ->label(__('messages.collections'))
                                ->badge(),
                        ]),
                ]),

            Section::make(__('admin.products.pricing'))
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextEntry::make('price')
                                ->label(__('messages.price'))
                                ->money('EUR'),
                            TextEntry::make('compare_price')
                                ->label(__('admin.products.compare_price'))
                                ->money('EUR'),
                            TextEntry::make('cost_price')
                                ->label(__('admin.products.cost_price'))
                                ->money('EUR'),
                        ]),
                ]),

            Section::make(__('admin.products.inventory'))
                ->schema([
                    Grid::make(5)
                        ->schema([
                            IconEntry::make('manage_stock')
                                ->label(__('admin.products.manage_stock'))
                                ->boolean(),
                            IconEntry::make('track_stock')
                                ->label(__('admin.products.track_stock'))
                                ->boolean(),
                            IconEntry::make('allow_backorder')
                                ->label(__('admin.products.allow_backorder'))
                                ->boolean(),
                            TextEntry::make('stock_quantity')
                                ->label(__('admin.products.stock_quantity')),
                            TextEntry::make('low_stock_threshold')
                                ->label(__('admin.products.low_stock_threshold')),
                        ]),
                ]),

            Section::make(__('admin.products.physical'))
                ->schema([
                    Grid::make(4)
                        ->schema([
                            TextEntry::make('weight')
                                ->label(__('admin.products.weight'))
                                ->suffix(' kg'),
                            TextEntry::make('length')
                                ->label(__('admin.products.length'))
                                ->suffix(' cm'),
                            TextEntry::make('width')
                                ->label(__('admin.products.width'))
                                ->suffix(' cm'),
                            TextEntry::make('height')
                                ->label(__('admin.products.height'))
                                ->suffix(' cm'),
                        ]),
                ])
                ->collapsible(),

            Section::make(__('admin.products.seo'))
                ->schema([
                    TextEntry::make('seo_title')
                        ->label(__('admin.products.seo_title')),
                    TextEntry::make('seo_description')
                        ->label(__('admin.products.seo_description'))
                        ->columnSpanFull(),
                ])
                ->collapsible()
                ->collapsed(),

            Section::make(__('admin.products.metadata'))
                ->schema([
                    KeyValueEntry::make('metadata')
                        ->label(__('admin.products.metadata')),
                ])
                ->collapsible()
                ->collapsed(),
        ]);
    }
}
