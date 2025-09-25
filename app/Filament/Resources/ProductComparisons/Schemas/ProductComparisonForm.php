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
            ->schema([
                Select::make('user_id')
                    ->label(__('product_comparisons.user'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('product_id')
                    ->label(__('product_comparisons.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('session_id')
                    ->label(__('product_comparisons.session_id'))
                    ->maxLength(255),
            ]);
    }
}
