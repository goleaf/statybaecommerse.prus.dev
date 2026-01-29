<?php

namespace App\Filament\Resources\CustomerGroups\Schemas;

use Filament\Infolists\Components\ColorEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;

class CustomerGroupInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SchemaSection::make(__('messages.basic_information'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                TextEntry::make('name')
                                    ->label(__('messages.name')),
                                TextEntry::make('code')
                                    ->label(__('messages.code')),
                                TextEntry::make('slug')
                                    ->label(__('messages.slug')),
                                TextEntry::make('type')
                                    ->label(__('messages.type'))
                                    ->badge(),
                                ColorEntry::make('color')
                                    ->label(__('messages.color')),
                                TextEntry::make('icon')
                                    ->label(__('messages.icon')),
                                TextEntry::make('sort_order')
                                    ->label(__('messages.sort_order')),
                            ]),
                        TextEntry::make('description')
                            ->label(__('messages.description'))
                            ->columnSpanFull(),
                    ]),

                SchemaSection::make(__('messages.discount_settings'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                TextEntry::make('discount_percentage')
                                    ->label(__('messages.discount_percentage'))
                                    ->suffix('%'),
                                TextEntry::make('discount_fixed')
                                    ->label(__('messages.discount_fixed'))
                                    ->money('EUR'),
                                IconEntry::make('has_special_pricing')
                                    ->label(__('messages.has_special_pricing'))
                                    ->boolean(),
                                IconEntry::make('has_volume_discounts')
                                    ->label(__('messages.has_volume_discounts'))
                                    ->boolean(),
                            ]),
                    ]),

                SchemaSection::make(__('messages.permissions'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                IconEntry::make('can_view_prices')
                                    ->label(__('messages.can_view_prices'))
                                    ->boolean(),
                                IconEntry::make('can_place_orders')
                                    ->label(__('messages.can_place_orders'))
                                    ->boolean(),
                                IconEntry::make('can_view_catalog')
                                    ->label(__('messages.can_view_catalog'))
                                    ->boolean(),
                                IconEntry::make('can_use_coupons')
                                    ->label(__('messages.can_use_coupons'))
                                    ->boolean(),
                            ]),
                    ]),

                SchemaSection::make(__('messages.financials'))
                    ->schema([
                        SchemaGrid::make(3)
                            ->schema([
                                TextEntry::make('minimum_order_amount')
                                    ->label(__('messages.minimum_order_amount'))
                                    ->money('EUR'),
                                TextEntry::make('credit_limit')
                                    ->label(__('messages.credit_limit'))
                                    ->money('EUR'),
                                TextEntry::make('payment_terms')
                                    ->label(__('messages.payment_terms')),
                            ]),
                    ]),

                SchemaSection::make(__('messages.status'))
                    ->schema([
                        SchemaGrid::make(3)
                            ->schema([
                                IconEntry::make('is_active')
                                    ->label(__('messages.active'))
                                    ->boolean(),
                                IconEntry::make('is_enabled')
                                    ->label(__('messages.enabled'))
                                    ->boolean(),
                                IconEntry::make('is_default')
                                    ->label(__('messages.default'))
                                    ->boolean(),
                            ]),
                    ]),
            ]);
    }
}
