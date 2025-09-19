<?php

namespace App\Filament\Resources\OrderShippings\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class OrderShippingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('order_id')
                    ->relationship('order', 'id')
                    ->required(),
                TextInput::make('carrier_name'),
                TextInput::make('tracking_number'),
                TextInput::make('tracking_url')
                    ->url(),
                TextInput::make('service'),
                DateTimePicker::make('shipped_at'),
                DateTimePicker::make('estimated_delivery'),
                DateTimePicker::make('delivered_at'),
                TextInput::make('weight')
                    ->numeric(),
                Textarea::make('dimensions')
                    ->columnSpanFull(),
                TextInput::make('cost')
                    ->numeric()
                    ->prefix('$'),
                Textarea::make('metadata')
                    ->columnSpanFull(),
            ]);
    }
}
