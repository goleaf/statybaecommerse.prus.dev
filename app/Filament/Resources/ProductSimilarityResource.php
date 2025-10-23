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
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

/**
 * ProductSimilarityResource
 *
 * Filament v4 resource for managing product similarity calculations in the
 * administration panel. Provides CRUD operations, filters and actions that
 * allow merchandising teams to audit how product similarity scores are
 * generated and adjusted.
 */
final class ProductSimilarityResource extends Resource
{
    protected static ?string $model = ProductSimilarity::class;

    protected static ?string $slug = 'product-similarities';

    protected static ?string $recordTitleAttribute = 'id';

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-squares-2x2';
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return NavigationGroup::Products;
    }

    public static function getNavigationSort(): ?int
    {
        return 16;
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

    public static function form(Form $form): Form
    {
        return $form->schema([
            SchemaSection::make(__('product_similarities.sections.products'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            Select::make('product_id')
                                ->label(__('product_similarities.fields.product'))
                                ->relationship('product', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            Select::make('similar_product_id')
                                ->label(__('product_similarities.fields.similar_product'))
                                ->relationship('similarProduct', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->rules(['different:product_id']),
                        ]),
                ]),
            SchemaSection::make(__('product_similarities.sections.similarity_details'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            Select::make('algorithm_type')
                                ->label(__('product_similarities.fields.algorithm_type'))
                                ->options(self::getAlgorithmOptions())
                                ->searchable()
                                ->required(),
                            TextInput::make('similarity_score')
                                ->label(__('product_similarities.fields.similarity_score'))
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(1)
                                ->step(0.000001)
                                ->required(),
                        ]),
                    KeyValue::make('calculation_data')
                        ->label(__('product_similarities.fields.calculation_data'))
                        ->columnSpanFull()
                        ->addActionLabel(__('product_similarities.actions.add_data_point'))
                        ->keyLabel(__('product_similarities.fields.data_point_key'))
                        ->valueLabel(__('product_similarities.fields.data_point_value')),
                ]),
            SchemaSection::make(__('product_similarities.sections.metadata'))
                ->schema([
                    SchemaGrid::make(1)
                        ->schema([
                            DateTimePicker::make('calculated_at')
                                ->label(__('product_similarities.fields.calculated_at'))
                                ->default(now())
                                ->required(),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label(__('product_similarities.fields.product'))
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('similarProduct.name')
                    ->label(__('product_similarities.fields.similar_product'))
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('algorithm_type')
                    ->label(__('product_similarities.fields.algorithm_type'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => self::getAlgorithmOptions()[$state] ?? (string) $state)
                    ->color(fn (?string $state): string => self::resolveAlgorithmColor($state))
                    ->sortable(),
                TextColumn::make('similarity_score')
                    ->label(__('product_similarities.fields.similarity_score'))
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (?string $state): string => self::resolveSimilarityColor((float) $state))
                    ->formatStateUsing(fn (?string $state): string => number_format((float) $state, 3)),
                TextColumn::make('calculated_at')
                    ->label(__('product_similarities.fields.calculated_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('product_similarities.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('product_similarities.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->label(__('product_similarities.fields.product'))
                    ->relationship('product', 'name')
                    ->searchable(),
                SelectFilter::make('similar_product_id')
                    ->label(__('product_similarities.fields.similar_product'))
                    ->relationship('similarProduct', 'name')
                    ->searchable(),
                SelectFilter::make('algorithm_type')
                    ->label(__('product_similarities.fields.algorithm_type'))
                    ->options(self::getAlgorithmOptions()),
                Filter::make('similarity_score_range')
                    ->form([
                        TextInput::make('min_score')
                            ->label(__('product_similarities.fields.min_score'))
                            ->numeric(),
                        TextInput::make('max_score')
                            ->label(__('product_similarities.fields.max_score'))
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $minScore = data_get($data, 'min_score');
                        $maxScore = data_get($data, 'max_score');

                        return $query
                            ->when($minScore !== null, fn (Builder $builder): Builder => $builder->where('similarity_score', '>=', (float) $minScore))
                            ->when($maxScore !== null, fn (Builder $builder): Builder => $builder->where('similarity_score', '<=', (float) $maxScore));
                    }),
                Filter::make('calculated_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label(__('product_similarities.fields.calculated_from')),
                        \Filament\Forms\Components\DatePicker::make('until')
                            ->label(__('product_similarities.fields.calculated_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $from = data_get($data, 'from');
                        $until = data_get($data, 'until');

                        return $query
                            ->when($from, fn (Builder $builder): Builder => $builder->whereDate('calculated_at', '>=', $from))
                            ->when($until, fn (Builder $builder): Builder => $builder->whereDate('calculated_at', '<=', $until));
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
            ])
            ->defaultSort('calculated_at', 'desc');
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['product', 'similarProduct']);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) ProductSimilarity::query()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    /**
     * @return array<string, string>
     */
    private static function getAlgorithmOptions(): array
    {
        return [
            'cosine_similarity' => __('product_similarities.algorithm_types.cosine_similarity'),
            'jaccard_similarity' => __('product_similarities.algorithm_types.jaccard_similarity'),
            'pearson_correlation' => __('product_similarities.algorithm_types.pearson_correlation'),
        ];
    }

    private static function resolveAlgorithmColor(?string $algorithm): string
    {
        return match ($algorithm) {
            'cosine_similarity' => 'success',
            'jaccard_similarity' => 'info',
            'pearson_correlation' => 'warning',
            default => 'gray',
        };
    }

    private static function resolveSimilarityColor(float $score): string
    {
        return match (true) {
            $score >= 0.8 => 'success',
            $score >= 0.6 => 'warning',
            default => 'danger',
        };
    }
}
