<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderShippings\Schemas;

use App\Models\Order;
use App\Support\Filament\Components\Flatpickr as SupportFlatpickr;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class OrderShippingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('order_id')
                    ->relationship(
                        name: 'order',
                        titleAttribute: 'number',
                        modifyQueryUsing: static fn (Builder $query): Builder => $query->withoutGlobalScopes(),
                    )
                    ->getOptionLabelFromRecordUsing(
                        static fn (Order $record): string => $record->number ?? __('Order #:id', ['id' => $record->getKey()]),
                    )
                    ->getOptionLabelUsing(
                        static fn (int|string|null $value): ?string => match (true) {
                            $value === null => null,
                            default         => optional(
                                Order::query()
                                    ->withoutGlobalScopes()
                                    ->find($value),
                            )->number ?? __('Order #:id', ['id' => $value]),
                        },
                    )
                    ->searchable()
                    ->required(),
                TextInput::make('carrier_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('service')
                    ->required()
                    ->maxLength(255),
                TextInput::make('tracking_number')
                    ->label(__('Tracking number'))
                    ->maxLength(255),
                TextInput::make('tracking_url')
                    ->url()
                    ->maxLength(500),
                SupportFlatpickr::makeDateTime('shipped_at'),
                SupportFlatpickr::makeDateTime('estimated_delivery'),
                SupportFlatpickr::makeDateTime('delivered_at'),
                TextInput::make('weight')
                    ->numeric()
                    ->step(0.001)
                    ->suffix('kg'),
                TextInput::make('dimensions')
                    ->helperText(__('Format: L × W × H (cm)')),
                TextInput::make('cost')
                    ->numeric()
                    ->step(0.01)
                    ->prefix('€'),
                KeyValue::make('metadata')
                    ->columnSpanFull()
                    ->keyLabel(__('Key'))
                    ->valueLabel(__('Value')),
            ]);
    }
}
