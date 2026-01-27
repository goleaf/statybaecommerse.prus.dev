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
                SchemaSection::make(__('Basic Information'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                TextEntry::make('name')
                                    ->label(__('Name')),
                                TextEntry::make('code')
                                    ->label(__('Code')),
                                TextEntry::make('slug')
                                    ->label(__('Slug')),
                                TextEntry::make('type')
                                    ->label(__('Type'))
                                    ->badge(),
                                ColorEntry::make('color')
                                    ->label(__('Color')),
                                TextEntry::make('icon')
                                    ->label(__('Icon')),
                                TextEntry::make('sort_order')
                                    ->label(__('Sort Order')),
                            ]),
                        TextEntry::make('description')
                            ->label(__('Description'))
                            ->columnSpanFull(),
                    ]),

                SchemaSection::make(__('Discount Settings'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                TextEntry::make('discount_percentage')
                                    ->label(__('Discount Percentage'))
                                    ->suffix('%'),
                                TextEntry::make('discount_fixed')
                                    ->label(__('Discount Fixed'))
                                    ->money('EUR'),
                                IconEntry::make('has_special_pricing')
                                    ->label(__('Has Special Pricing'))
                                    ->boolean(),
                                IconEntry::make('has_volume_discounts')
                                    ->label(__('Has Volume Discounts'))
                                    ->boolean(),
                            ]),
                    ]),

                SchemaSection::make(__('Permissions'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                IconEntry::make('can_view_prices')
                                    ->label(__('Can View Prices'))
                                    ->boolean(),
                                IconEntry::make('can_place_orders')
                                    ->label(__('Can Place Orders'))
                                    ->boolean(),
                                IconEntry::make('can_view_catalog')
                                    ->label(__('Can View Catalog'))
                                    ->boolean(),
                                IconEntry::make('can_use_coupons')
                                    ->label(__('Can Use Coupons'))
                                    ->boolean(),
                            ]),
                    ]),

                SchemaSection::make(__('Financials'))
                    ->schema([
                        SchemaGrid::make(3)
                            ->schema([
                                TextEntry::make('minimum_order_amount')
                                    ->label(__('Minimum Order Amount'))
                                    ->money('EUR'),
                                TextEntry::make('credit_limit')
                                    ->label(__('Credit Limit'))
                                    ->money('EUR'),
                                TextEntry::make('payment_terms')
                                    ->label(__('Payment Terms')),
                            ]),
                    ]),

                SchemaSection::make(__('Status'))
                    ->schema([
                        SchemaGrid::make(3)
                            ->schema([
                                IconEntry::make('is_active')
                                    ->label(__('Active'))
                                    ->boolean(),
                                IconEntry::make('is_enabled')
                                    ->label(__('Enabled'))
                                    ->boolean(),
                                IconEntry::make('is_default')
                                    ->label(__('Default'))
                                    ->boolean(),
                            ]),
                    ]),
            ]);
    }
}
