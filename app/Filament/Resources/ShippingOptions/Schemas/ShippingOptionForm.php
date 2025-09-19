<?php

namespace App\Filament\Resources\ShippingOptions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ShippingOptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('carrier_name')
                    ->required(),
                TextInput::make('service_type')
                    ->required(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('currency_code')
                    ->required()
                    ->default('EUR'),
                Select::make('zone_id')
                    ->relationship('zone', 'name')
                    ->required(),
                Toggle::make('is_enabled')
                    ->required(),
                Toggle::make('is_default')
                    ->required(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('min_weight')
                    ->numeric(),
                TextInput::make('max_weight')
                    ->numeric(),
                TextInput::make('min_order_amount')
                    ->numeric(),
                TextInput::make('max_order_amount')
                    ->numeric(),
                TextInput::make('estimated_days_min')
                    ->numeric(),
                TextInput::make('estimated_days_max')
                    ->numeric(),
                Textarea::make('metadata')
                    ->columnSpanFull(),
            ]);
    }
}
