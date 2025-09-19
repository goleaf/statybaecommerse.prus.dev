<?php

namespace App\Filament\Resources\ProductSimilarities\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ProductSimilarityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required(),
                Select::make('similar_product_id')
                    ->relationship('similarProduct', 'name')
                    ->required(),
                TextInput::make('algorithm_type')
                    ->required(),
                TextInput::make('similarity_score')
                    ->required()
                    ->numeric(),
                Textarea::make('calculation_data')
                    ->columnSpanFull(),
                DateTimePicker::make('calculated_at')
                    ->required(),
            ]);
    }
}
