<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductSimilarities\Schemas;

use App\Models\Product;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class ProductSimilarityForm
{
    public static function configure(Form $schema): Form
    {
        return $schema
            ->schema([
                Select::make('product_id')
                    ->label(__('product_similarities.product'))
                    ->options(Product::query()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Select::make('similar_product_id')
                    ->label(__('product_similarities.similar_product'))
                    ->options(Product::query()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Select::make('algorithm_type')
                    ->label(__('product_similarities.algorithm_type'))
                    ->options([
                        'cosine_similarity'  => __('product_similarities.algorithms.cosine_similarity'),
                        'jaccard_similarity' => __('product_similarities.algorithms.jaccard_similarity'),
                    ])
                    ->required(),
                TextInput::make('similarity_score')
                    ->label(__('product_similarities.similarity_score'))
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(1)
                    ->step(0.01)
                    ->required(),
                KeyValue::make('calculation_data')
                    ->label(__('product_similarities.similarity_data'))
                    ->columnSpanFull(),
            ]);
    }
}
