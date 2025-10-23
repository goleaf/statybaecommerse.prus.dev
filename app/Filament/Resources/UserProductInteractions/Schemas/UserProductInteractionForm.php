<?php

namespace App\Filament\Resources\UserProductInteractions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;

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
                Flatpickr::make('first_interaction')
                    ->time(true)
                    ->time24hr(true)
                    ->seconds(false)
                    ->format('Y-m-d H:i')
                    ->required(),
                Flatpickr::make('last_interaction')
                    ->time(true)
                    ->time24hr(true)
                    ->seconds(false)
                    ->format('Y-m-d H:i')
                    ->required(),
            ]);
    }
}
