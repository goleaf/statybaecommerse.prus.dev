<?php

namespace App\Filament\Resources\ProductSimilarities\Schemas;

use App\Models\Product;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Schema;

class ProductSimilarityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('product_id')
                    ->label('admin.product_similarity.product1')
                    ->options(Product::query()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Select::make('similar_product_id')
                    ->label('admin.product_similarity.product2')
                    ->options(Product::query()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Select::make('algorithm_type')
                    ->label('admin.product_similarity.algorithm_type')
                    ->options([
                        'cosine_similarity' => 'Cosine similarity',
                        'jaccard_similarity' => 'Jaccard similarity',
                    ])
                    ->required(),
                TextInput::make('similarity_score')
                    ->label('admin.product_similarity.similarity_score')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(1)
                    ->step(0.01)
                    ->required(),
                KeyValue::make('calculation_data')
                    ->label('admin.product_similarity.similarity_data')
                    ->columnSpanFull(),
            ]);
    }
}
