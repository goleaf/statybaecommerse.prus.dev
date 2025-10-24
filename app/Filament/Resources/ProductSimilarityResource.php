<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ProductSimilarityResource\Pages;
use App\Models\ProductSimilarity;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class ProductSimilarityResource extends Resource
{
    protected static ?string $model = ProductSimilarity::class;

    protected static ?string $slug = 'product-similarities';

    protected static ?string $recordTitleAttribute = 'id';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationGroup(): \UnitEnum|string|null
    {
        return NavigationGroup::Products->value;
    }

    public static function getNavigationLabel(): string
    {
        return __('product_similarities.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('product_similarities.plural');
    }

    public static function getModelLabel(): string
    {
        return __('product_similarities.single');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            SchemaSection::make(__('product_similarities.basic_information'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
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
                                ->required()
                                ->rule('different:product_id'),
                        ]),
                    SchemaGrid::make(2)
                        ->schema([
                            Select::make('algorithm_type')
                                ->label(__('product_similarities.algorithm_type'))
                                ->options(self::getAlgorithmOptions())
                                ->required(),
                            TextInput::make('similarity_score')
                                ->label(__('product_similarities.similarity_score'))
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(1)
                                ->step(0.000001)
                                ->required(),
                        ]),
                    KeyValue::make('calculation_data')
                        ->label(__('product_similarities.calculation_data'))
                        ->helperText(__('product_similarities.calculation_data_help'))
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
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
                TextColumn::make('algorithm_type')
                    ->label(__('product_similarities.algorithm_type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::getAlgorithmOptions()[$state] ?? $state)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('similarity_score')
                    ->label(__('product_similarities.similarity_score'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('calculated_at')
                    ->label(__('product_similarities.calculated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('product_id')
                    ->label(__('product_similarities.filters.product'))
                    ->relationship('product', 'name'),
                SelectFilter::make('similar_product_id')
                    ->label(__('product_similarities.filters.similar_product'))
                    ->relationship('similarProduct', 'name'),
                SelectFilter::make('algorithm_type')
                    ->label(__('product_similarities.filters.algorithm_type'))
                    ->options(self::getAlgorithmOptions()),
                Filter::make('similarity_score_range')
                    ->form([
                        TextInput::make('min_score')
                            ->label(__('product_similarities.filters.min_score'))
                            ->numeric(),
                        TextInput::make('max_score')
                            ->label(__('product_similarities.filters.max_score'))
                            ->numeric(),
                    ])
                    ->query(static fn (Builder $query, array $data): Builder => $query
                        ->when(
                            filled($data['min_score'] ?? null),
                            static fn (Builder $innerQuery): Builder => $innerQuery->where('similarity_score', '>=', (float) $data['min_score'])
                        )
                        ->when(
                            filled($data['max_score'] ?? null),
                            static fn (Builder $innerQuery): Builder => $innerQuery->where('similarity_score', '<=', (float) $data['max_score'])
                        )),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('similarity_score', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductSimilarities::route('/'),
            'create' => Pages\CreateProductSimilarity::route('/create'),
            'view' => Pages\ViewProductSimilarity::route('/{record}'),
            'edit' => Pages\EditProductSimilarity::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function getAlgorithmOptions(): array
    {
        return [
            'cosine_similarity' => __('product_similarities.algorithm_types.cosine'),
            'jaccard_similarity' => __('product_similarities.algorithm_types.jaccard'),
        ];
    }
}
