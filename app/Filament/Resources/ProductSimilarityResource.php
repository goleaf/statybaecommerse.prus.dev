<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ProductSimilarityResource\Pages;
use App\Models\ProductSimilarity;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class ProductSimilarityResource extends BaseResource
{
    protected static ?string $model = ProductSimilarity::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Inventory;

    protected static ?int $navigationSort = 6;

    public static function getNavigationLabel(): string
    {
        return __('admin.product_similarities.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.product_similarities.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.product_similarities.model_label');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            SchemaSection::make(__('admin.product_similarities.basic_information'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            Select::make('product_id')
                                ->label(__('messages.product'))
                                ->relationship('product', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            Select::make('similar_product_id')
                                ->label(__('admin.products.similar_product'))
                                ->relationship('similarProduct', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            TextInput::make('algorithm_type')
                                ->label(__('admin.products.algorithm_type'))
                                ->required()
                                ->maxLength(100),
                            TextInput::make('similarity_score')
                                ->label(__('admin.products.similarity_score'))
                                ->numeric()
                                ->required(),
                            DateTimePicker::make('calculated_at')
                                ->label(__('admin.products.calculated_at')),
                        ]),
                    KeyValue::make('calculation_data')
                        ->label(__('admin.products.calculation_data'))
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label(__('messages.product'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('similarProduct.name')
                    ->label(__('admin.products.similar_product'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('algorithm_type')
                    ->label(__('admin.products.algorithm_type'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('similarity_score')
                    ->label(__('admin.products.similarity_score'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('calculated_at')
                    ->label(__('admin.products.calculated_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('algorithm_type')
                    ->label(__('admin.products.algorithm_type'))
                    ->options(static fn (): array => ProductSimilarity::query()
                        ->withoutGlobalScopes()
                        ->whereNotNull('algorithm_type')
                        ->distinct()
                        ->pluck('algorithm_type', 'algorithm_type')
                        ->toArray()),
                Filter::make('similarity_score_range')
                    ->label(__('admin.products.similarity_score_range'))
                    ->form([
                        TextInput::make('min_score')
                            ->label(__('admin.products.min_score'))
                            ->numeric(),
                        TextInput::make('max_score')
                            ->label(__('admin.products.max_score'))
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $min = $data['min_score'] ?? null;
                        $max = $data['max_score'] ?? null;

                        if ($min !== null && $min !== '') {
                            $query->where('similarity_score', '>=', (float) $min);
                        }

                        if ($max !== null && $max !== '') {
                            $query->where('similarity_score', '<=', (float) $max);
                        }

                        return $query;
                    }),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('similarity_score', 'desc');
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
}
