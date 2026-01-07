<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserProductInteractions\Schemas;

use App\Support\Filament\Components\Flatpickr as SupportFlatpickr;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserProductInteractionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required(),
                Select::make('product_variant_id')
                    ->relationship('variant', 'name')
                    ->searchable()
                    ->nullable(),
                TextInput::make('event')
                    ->label(__('admin.user_product_interactions.event'))
                    ->required(),
                SupportFlatpickr::makeDateTime('occurred_at')
                    ->label(__('admin.user_product_interactions.occurred_at'))
                    ->default(now())
                    ->required(),
                KeyValue::make('meta')
                    ->label(__('admin.user_product_interactions.meta'))
                    ->keyLabel('Key')
                    ->valueLabel('Value')
                    ->columnSpanFull(),
            ]);
    }
}
