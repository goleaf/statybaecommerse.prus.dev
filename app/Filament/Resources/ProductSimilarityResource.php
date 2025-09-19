<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ProductSimilarityResource\Pages;
use App\Models\Product;
use App\Models\ProductSimilarity;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * ProductSimilarityResource
 *
 * Filament v4 resource for ProductSimilarity management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class ProductSimilarityResource extends Resource
{
    protected static ?string $model = ProductSimilarity::class;

    // protected static $navigationGroup = NavigationGroup::System;

    protected static ?int $navigationSort = 16;

    protected static ?string $recordTitleAttribute = 'algorithm_type';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('product_similarities.title');
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('product_similarities.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('product_similarities.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            SchemaSection::make(__('product_similarities.basic_information'))
                ->components([
                    Grid::make(2)->components([
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
                    ]),
                    Grid::make(2)->components([
                        TextInput::make('algorithm_type')
                            ->label(__('product_similarities.algorithm_type'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('similarity_score')
                            ->label(__('product_similarities.similarity_score'))
                            ->numeric()
                            ->step(0.000001)
                            ->minValue(0)
                            ->maxValue(1)
                            ->required(),
                    ]),
                    Textarea::make('calculation_data')
                        ->label(__('product_similarities.calculation_data'))
                        ->rows(3)
                        ->helperText(__('product_similarities.calculation_data_help')),
                ]),
        ]);
    }

    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label(__('product_similarities.product'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('similarProduct.name')
                    ->label(__('product_similarities.similar_product'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                TextColumn::make('algorithm_type')
                    ->label(__('product_similarities.algorithm_type'))
                    ->searchable()
                    ->sortable()
                    ->badge(),
                TextColumn::make('similarity_score')
                    ->label(__('product_similarities.similarity_score'))
                    ->numeric(decimalPlaces: 6)
                    ->sortable()
                    ->color(fn($state): string => match (true) {
                        $state >= 0.8 => 'success',
                        $state >= 0.6 => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('calculated_at')
                    ->label(__('product_similarities.calculated_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('product_similarities.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('product_similarities.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('algorithm_type')
                    ->label(__('product_similarities.algorithm_type'))
                    ->options([
                        'cosine_similarity' => 'Cosine Similarity',
                        'jaccard_similarity' => 'Jaccard Similarity',
                        'euclidean_distance' => 'Euclidean Distance',
                        'pearson_correlation' => 'Pearson Correlation',
                        'content_based' => 'Content Based',
                        'collaborative_filtering' => 'Collaborative Filtering',
                    ]),
                SelectFilter::make('product_id')
                    ->label(__('product_similarities.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('similarity_score_range')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('min_score')
                            ->label(__('product_similarities.min_score'))
                            ->numeric()
                            ->step(0.1)
                            ->minValue(0)
                            ->maxValue(1),
                        \Filament\Forms\Components\TextInput::make('max_score')
                            ->label(__('product_similarities.max_score'))
                            ->numeric()
                            ->step(0.1)
                            ->minValue(0)
                            ->maxValue(1),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_score'],
                                fn(Builder $query, $score): Builder => $query->where('similarity_score', '>=', $score),
                            )
                            ->when(
                                $data['max_score'],
                                fn(Builder $query, $score): Builder => $query->where('similarity_score', '<=', $score),
                            );
                    }),
                Filter::make('calculated_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('calculated_from')
                            ->label(__('product_similarities.calculated_from')),
                        \Filament\Forms\Components\DatePicker::make('calculated_until')
                            ->label(__('product_similarities.calculated_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['calculated_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('calculated_at', '>=', $date),
                            )
                            ->when(
                                $data['calculated_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('calculated_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('similarity_score', 'desc');
    }

    /**
     * Get the relations for this resource.
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the pages for this resource.
     * @return array
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductSimilarities::route('/'),
            'create' => Pages\CreateProductSimilarity::route('/create'),
            'view' => Pages\ViewProductSimilarity::route('/{record}'),
            'edit' => Pages\EditProductSimilarity::route('/{record}/edit'),
        ];
    }
}
