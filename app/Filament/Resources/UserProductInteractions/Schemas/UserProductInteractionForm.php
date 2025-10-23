<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserProductInteractions\Schemas;

use App\Support\Filament\Components\Flatpickr as SupportFlatpickr;
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
                TextInput::make('interaction_type')
                    ->required(),
                TextInput::make('rating')
                    ->numeric(),
                TextInput::make('count')
                    ->required()
                    ->numeric()
                    ->default(1),
                SupportFlatpickr::makeDateTime('first_interaction')
                    ->default(now())
                    ->required(),
                SupportFlatpickr::makeDateTime('last_interaction')
                    ->default(now())
                    ->required(),
            ]);
    }
}
