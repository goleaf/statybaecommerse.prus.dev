<?php

namespace App\Filament\Resources\ProductSimilarities\Schemas;

use App\Models\Product;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductSimilarityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('product1_id')
                    ->label('admin.product_similarity.product1')
                    ->options(Product::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Select::make('product2_id')
                    ->label('admin.product_similarity.product2')
                    ->options(Product::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                TextInput::make('similarity_score')
                    ->label('admin.product_similarity.similarity_score')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(1)
                    ->step(0.01)
                    ->required(),
                TextInput::make('similarity_data')
                    ->label('admin.product_similarity.similarity_data')
                    ->json()
                    ->columnSpanFull(),
            ]);
    }
}
