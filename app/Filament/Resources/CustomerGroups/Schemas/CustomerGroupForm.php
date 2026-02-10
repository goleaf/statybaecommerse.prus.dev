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
                SchemaSection::make(__('messages.general'))
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
                                    ->helperText(__('messages.automatically_generated_from_name_or_code')),
                                Select::make('type')
                                    ->label(__('messages.type'))
                                    ->options([
                                        'retail'    => __('messages.retail'),
                                        'wholesale' => __('messages.wholesale'),
                                        'b2b'       => __('messages.b2b'),
                                        'internal'  => __('messages.internal'),
                                    ])
                                    ->required(),
                                ColorPicker::make('color')
                                    ->label(__('messages.color')),
                                TextInput::make('icon')
                                    ->label(__('admin.news_images.image'))
                                    ->maxLength(255),
                                TextInput::make('sort_order')
                                    ->label(__('messages.sort'))
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
                                    ->label(__('ui.has_special_pricing')),
                                Toggle::make('has_volume_discounts')
                                    ->label(__('ui.has_volume_discounts')),
                            ]),
                    ])
                    ->columnSpanFull(),

                SchemaSection::make(__('ui.security'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                Toggle::make('can_view_prices')
                                    ->label(__('ui.can_view_prices'))
                                    ->default(true),
                                Toggle::make('can_place_orders')
                                    ->label(__('ui.can_place_orders'))
                                    ->default(true),
                                Toggle::make('can_view_catalog')
                                    ->label(__('ui.can_view_catalog'))
                                    ->default(true),
                                Toggle::make('can_use_coupons')
                                    ->label(__('ui.can_use_coupons'))
                                    ->default(true),
                            ]),
                    ])
                    ->columnSpanFull(),

                SchemaSection::make(__('messages.financials'))
                    ->schema([
                        SchemaGrid::make(3)
                            ->schema([
                                TextInput::make('minimum_order_amount')
                                    ->label(__('ui.minimum_order_amount'))
                                    ->numeric()
                                    ->minValue(0),
                                TextInput::make('credit_limit')
                                    ->label(__('ui.credit_limit'))
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
