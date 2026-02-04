<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerGroups\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;

class CustomerGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SchemaSection::make(__('messages.General'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('messages.name'))
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('code')
                                    ->label(__('messages.code'))
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(50),
                                TextInput::make('slug')
                                    ->label(__('messages.slug'))
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->helperText(__('Automatically generated from name or code.')),
                                Select::make('type')
                                    ->label(__('messages.type'))
                                    ->options([
                                        'retail'    => __('messages.Retail'),
                                        'wholesale' => __('messages.Wholesale'),
                                        'b2b'       => __('messages.B2B'),
                                        'internal'  => __('messages.Internal'),
                                    ])
                                    ->required(),
                                ColorPicker::make('color')
                                    ->label(__('messages.Color')),
                                TextInput::make('icon')
                                    ->label(__('admin.news_images.image'))
                                    ->maxLength(255),
                                TextInput::make('sort_order')
                                    ->label(__('messages.Sort'))
                                    ->numeric()
                                    ->default(0),
                            ]),
                        Textarea::make('description')
                            ->label(__('messages.description'))
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                SchemaSection::make(__('messages.discounts'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                TextInput::make('discount_percentage')
                                    ->label(__('admin.products.price_increase_percentage'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->suffix('%'),
                                TextInput::make('discount_fixed')
                                    ->label(__('messages.discount_amount'))
                                    ->numeric()
                                    ->minValue(0),
                                Toggle::make('has_special_pricing')
                                    ->label(__('Has Special Pricing')),
                                Toggle::make('has_volume_discounts')
                                    ->label(__('Has Volume Discounts')),
                            ]),
                    ])
                    ->columnSpanFull(),

                SchemaSection::make(__('Security'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                Toggle::make('can_view_prices')
                                    ->label(__('Can View Prices'))
                                    ->default(true),
                                Toggle::make('can_place_orders')
                                    ->label(__('Can Place Orders'))
                                    ->default(true),
                                Toggle::make('can_view_catalog')
                                    ->label(__('Can View Catalog'))
                                    ->default(true),
                                Toggle::make('can_use_coupons')
                                    ->label(__('Can Use Coupons'))
                                    ->default(true),
                            ]),
                    ])
                    ->columnSpanFull(),

                SchemaSection::make(__('messages.financials'))
                    ->schema([
                        SchemaGrid::make(3)
                            ->schema([
                                TextInput::make('minimum_order_amount')
                                    ->label(__('Minimum Order Amount'))
                                    ->numeric()
                                    ->minValue(0),
                                TextInput::make('credit_limit')
                                    ->label(__('Credit Limit'))
                                    ->numeric()
                                    ->minValue(0),
                                TextInput::make('payment_terms')
                                    ->label(__('messages.payment_method'))
                                    ->placeholder('net_30')
                                    ->maxLength(255),
                            ]),
                    ])
                    ->columnSpanFull(),

                SchemaSection::make(__('messages.status'))
                    ->schema([
                        SchemaGrid::make(3)
                            ->schema([
                                Toggle::make('is_active')
                                    ->label(__('messages.active'))
                                    ->default(true),
                                Toggle::make('is_enabled')
                                    ->label(__('messages.enabled'))
                                    ->default(true),
                                Toggle::make('is_default')
                                    ->label(__('messages.default'))
                                    ->default(false),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
