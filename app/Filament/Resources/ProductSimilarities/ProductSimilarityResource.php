<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductSimilarities;
use App\Support\Concerns\HasNav;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ProductSimilarities\Pages\CreateProductSimilarity;
use App\Filament\Resources\ProductSimilarities\Pages\EditProductSimilarity;
use App\Filament\Resources\ProductSimilarities\Pages\ListProductSimilarities;
use App\Models\ProductSimilarity;
use BackedEnum;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductSimilarityResource extends Resource
{
    use HasNav;

    protected static ?string $model = ProductSimilarity::class;

    /**
     * @var string|BackedEnum|null Pin the resource under the analytics icon set for quick discovery.
     */
    protected static $navigationIcon = 'heroicon-o-rectangle-stack';

    /**
     * @var string|BackedEnum|null Persist the navigation group inside the analytics cluster.
     */
    protected static $navigationGroup = NavigationGroup::Analytics;

    public static function getNavigationGroup(): ?string
    {
        // Convert enum navigation groups into their translated label for Filament's sidebar.
        $group = static::$navigationGroup;

        return $group instanceof NavigationGroup ? $group->label() : $group;
    }

    public static function getNavigationLabel(): string
    {
        // Reuse translation keys so both English and Lithuanian panels stay in sync.
        return __('product_similarities.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        // Provide a descriptive plural label for grid headers and breadcrumbs.
        return __('product_similarities.plural');
    }

    public static function getModelLabel(): string
    {
        // Ensure singular references (e.g. edit pages) stay localised.
        return __('product_similarities.single');
    }

    public static function form(Form $form): Form
    {
        // Build the management form so operators can curate similarity pairs quickly.
        return $form->schema([
            Select::make('product_id')
                ->label(__('product_similarities.product'))
                ->relationship('product', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Select::make('similar_product_id')
                ->label(__('product_similarities.similar_product'))
                ->relationship('similarProduct', 'name')
                ->searchable()
                ->preload()
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

    public static function table(Table $table): Table
    {
        // Surface the cached similarity results with filtering helpers for analysts.
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
                TextColumn::make('algorithm_type')
                    ->label(__('product_similarities.algorithm_type'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('similarity_score')
                    ->label(__('product_similarities.similarity_score'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin.common.updated_at'))
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
                        TextInput::make('min_score')
                            ->numeric()
                            ->label(__('product_similarities.filters.min_score')),
                        TextInput::make('max_score')
                            ->numeric()
                            ->label(__('product_similarities.filters.max_score')),
                    ])
                    ->query(static function (Builder $query, array $data): Builder {
                        // Apply lower and upper bounds only when the operator supplies values.
                        return $query
                            ->when(
                                isset($data['min_score']) && $data['min_score'] !== null,
                                static fn (Builder $builder): Builder => $builder->where('similarity_score', '>=', (float) $data['min_score'])
                            )
                            ->when(
                                isset($data['max_score']) && $data['max_score'] !== null,
                                static fn (Builder $builder): Builder => $builder->where('similarity_score', '<=', (float) $data['max_score'])
                            );
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListProductSimilarities::route('/'),
            'create' => CreateProductSimilarity::route('/create'),
            'edit'   => EditProductSimilarity::route('/{record}/edit'),
        ];
    }
}
