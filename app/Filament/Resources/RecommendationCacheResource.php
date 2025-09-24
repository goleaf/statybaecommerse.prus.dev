<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\RecommendationCacheResource\Pages;
use App\Models\Product;
use App\Models\RecommendationBlock;
use App\Models\RecommendationCache;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

/**
 * RecommendationCacheResource
 *
 * Filament v4 resource for RecommendationCache management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class RecommendationCacheResource extends Resource
{
    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'Analytics';
    }

    protected static ?string $model = RecommendationCache::class;

    protected static ?int $navigationSort = 20;

    protected static ?string $recordTitleAttribute = 'cache_key';

    public static function getNavigationLabel(): string
    {
        return __('admin.recommendation_caches.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.recommendation_caches.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.recommendation_caches.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaSection::make(__('admin.recommendation_caches.basic_information'))
                    ->schema([
                        SchemaGrid::make(2)
                            ->schema([
                                TextInput::make('cache_key')
                                    ->label(__('admin.recommendation_caches.cache_key'))
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(RecommendationCache::class, 'cache_key', ignoreRecord: true),
                                Select::make('block_id')
                                    ->label(__('admin.recommendation_caches.block'))
                                    ->options(RecommendationBlock::pluck('name', 'id'))
                                    ->required()
                                    ->searchable(),
                                Select::make('user_id')
                                    ->label(__('admin.recommendation_caches.user'))
                                    ->options(User::pluck('name', 'id'))
                                    ->required()
                                    ->searchable(),
                                Select::make('product_id')
                                    ->label(__('admin.recommendation_caches.product'))
                                    ->options(Product::pluck('name', 'id'))
                                    ->searchable(),
                                TextInput::make('context_type')
                                    ->label(__('admin.recommendation_caches.context_type'))
                                    ->maxLength(100),
                                TextInput::make('hit_count')
                                    ->label(__('admin.recommendation_caches.hit_count'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0),
                                KeyValue::make('recommendations')
                                    ->label(__('admin.recommendation_caches.recommendations'))
                                    ->default([])
                                    ->columnSpanFull(),
                            ]),
                        DateTimePicker::make('expires_at')
                            ->label(__('admin.recommendation_caches.expires_at'))
                            ->required()
                            ->default(now()->addHours(24)),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cache_key')
                    ->label(__('admin.recommendation_caches.cache_key'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 30 ? $state : null;
                    }),
                TextColumn::make('block.name')
                    ->label(__('admin.recommendation_caches.block'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label(__('admin.recommendation_caches.user'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label(__('admin.recommendation_caches.product'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 30 ? $state : null;
                    }),
                TextColumn::make('context_type')
                    ->label(__('admin.recommendation_caches.context_type'))
                    ->badge()
                    ->color('info'),
                TextColumn::make('hit_count')
                    ->label(__('admin.recommendation_caches.hit_count'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->label(__('admin.recommendation_caches.expires_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('block_id')
                    ->label(__('admin.recommendation_caches.block'))
                    ->options(RecommendationBlock::pluck('name', 'id'))
                    ->searchable(),
                SelectFilter::make('user_id')
                    ->label(__('admin.recommendation_caches.user'))
                    ->options(User::pluck('name', 'id'))
                    ->searchable(),
                SelectFilter::make('product_id')
                    ->label(__('admin.recommendation_caches.product'))
                    ->options(Product::pluck('name', 'id'))
                    ->searchable(),
                SelectFilter::make('context_type')
                    ->label(__('admin.recommendation_caches.context_type'))
                    ->options([
                        'homepage' => 'Homepage',
                        'product' => 'Product',
                        'category' => 'Category',
                        'cart' => 'Cart',
                        'checkout' => 'Checkout',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('expires_at', 'desc');
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
            'index' => Pages\ListRecommendationCaches::route('/'),
            'create' => Pages\CreateRecommendationCache::route('/create'),
            'view' => Pages\ViewRecommendationCache::route('/{record}'),
            'edit' => Pages\EditRecommendationCache::route('/{record}/edit'),
        ];
    }
}
