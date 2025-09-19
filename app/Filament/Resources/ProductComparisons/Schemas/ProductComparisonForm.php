<?php

namespace App\Filament\Resources\ProductComparisons\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductComparisonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('session_id'),
                Select::make('user_id')
                    ->relationship('user', 'name'),
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required(),
            ]);
    }
}
