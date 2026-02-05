<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductFeatureResource\Schemas;

use App\Models\ProductFeature;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductFeatureInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.product_features.basic_information'))
                    ->schema([
                        TextEntry::make('product.name')
                            ->label(__('messages.product')),
                        TextEntry::make('feature_type'),
                        TextEntry::make('feature_key'),
                        TextEntry::make('feature_value'),
                        TextEntry::make('weight'),
                        IconEntry::make('is_active')
                            ->boolean(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make(__('messages.attached_products'))
                    ->schema([
                        RepeatableEntry::make('attached_products')
                            ->hiddenLabel()
                            ->getStateUsing(static function (ProductFeature $record): array {
                                $record->loadMissing(['product.primaryImage']);

                                return $record->product ? [$record->product] : [];
                            })
                            ->schema([
                                ImageEntry::make('primaryImage.path')
                                    ->label(__('messages.image'))
                                    ->disk('public')
                                    ->defaultImageUrl(product_placeholder_url('thumb'))
                                    ->square(),
                                TextEntry::make('name')
                                    ->label(__('messages.name')),
                                TextEntry::make('sku')
                                    ->label(__('messages.sku')),
                                TextEntry::make('price')
                                    ->label(__('messages.price'))
                                    ->money('EUR'),
                            ])
                            ->table([
                                TableColumn::make(__('messages.image')),
                                TableColumn::make(__('messages.name')),
                                TableColumn::make(__('messages.sku')),
                                TableColumn::make(__('messages.price')),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
