<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductSimilarities\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Schema;

class ProductSimilaritiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label(__('product_similarities.product'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('similarProduct.name')
                    ->label(__('product_similarities.similar_product'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('similarity_score')
                    ->label(__('product_similarities.similarity_score'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('admin.common.created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('admin.common.updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->label(__('product_similarities.filters.product'))
                    ->relationship('product', 'name'),
                SelectFilter::make('similar_product_id')
                    ->label(__('product_similarities.filters.similar_product'))
                    ->relationship('similarProduct', 'name'),
                SelectFilter::make('algorithm_type')
                    ->label(__('product_similarities.filters.algorithm_type'))
                    ->options([
                        'cosine_similarity'  => __('product_similarities.algorithms.cosine_similarity'),
                        'jaccard_similarity' => __('product_similarities.algorithms.jaccard_similarity'),
                    ]),
                Filter::make('similarity_score_range')
                    ->form([
                        TextInput::make('min_score')->numeric()->label(__('product_similarities.filters.min_score')),
                        TextInput::make('max_score')->numeric()->label(__('product_similarities.filters.max_score')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(isset($data['min_score']) && $data['min_score'] !== null, fn (Builder $builder) => $builder->where('similarity_score', '>=', (float) $data['min_score']))
                            ->when(isset($data['max_score']) && $data['max_score'] !== null, fn (Builder $builder) => $builder->where('similarity_score', '<=', (float) $data['max_score']));
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
