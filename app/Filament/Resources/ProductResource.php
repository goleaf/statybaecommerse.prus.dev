<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers\AttributesRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\CategoriesRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\CollectionsRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\DocumentsRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\ImagesRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\ReviewsRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\VariantsRelationManager;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * ProductResource
 *
 * Filament v4 resource for Product management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    public static function getNavigationIcon(): BackedEnum|Htmlable|string|null
    {
        return 'heroicon-o-cube';
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'Products';
    }

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationLabel(): string
    {
        return __('products.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('products.plural');
    }

    public static function getModelLabel(): string
    {
        return __('products.single');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('Product Information')
                    ->tabs([
                        Tab::make('Basic Information')
                            ->schema([
                                Section::make('Product Details')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label(__('products.fields.name'))
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->live()
                                                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Str::slug($state))),
                                                TextInput::make('slug')
                                                    ->label(__('products.fields.slug'))
                                                    ->required()
                                                    ->unique(ignoreRecord: true)
                                                    ->maxLength(255),
                                            ]),
                                        TextInput::make('sku')
                                            ->label(__('products.fields.sku'))
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255),
                                        TextInput::make('barcode')
                                            ->label(__('products.fields.barcode'))
                                            ->maxLength(255),
                                        RichEditor::make('description')
                                            ->label(__('products.fields.description'))
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'underline',
                                                'strike',
                                                'link',
                                                'bulletList',
                                                'orderedList',
                                            ]),
                                        Textarea::make('short_description')
                                            ->label(__('products.fields.short_description'))
                                            ->rows(3)
                                            ->maxLength(500),
                                    ]),
                                Section::make('Pricing & Inventory')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('price')
                                                    ->label(__('products.fields.price'))
                                                    ->required()
                                                    ->numeric()
                                                    ->prefix('€')
                                                    ->step(0.01),
                                                TextInput::make('compare_price')
                                                    ->label(__('products.fields.compare_price'))
                                                    ->numeric()
                                                    ->prefix('€')
                                                    ->step(0.01),
                                                TextInput::make('cost_price')
                                                    ->label(__('products.fields.cost_price'))
                                                    ->numeric()
                                                    ->prefix('€')
                                                    ->step(0.01),
                                            ]),
                                        Grid::make(2)
                                            ->schema([
                                                Toggle::make('manage_stock')
                                                    ->label(__('products.fields.manage_stock'))
                                                    ->default(true),
                                                Toggle::make('track_stock')
                                                    ->label(__('products.fields.track_stock'))
                                                    ->default(true),
                                            ]),
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('stock_quantity')
                                                    ->label(__('products.fields.stock_quantity'))
                                                    ->numeric()
                                                    ->default(0),
                                                TextInput::make('low_stock_threshold')
                                                    ->label(__('products.fields.low_stock_threshold'))
                                                    ->numeric()
                                                    ->default(5),
                                            ]),
                                    ]),
                            ]),
                        Tab::make('Media & SEO')
                            ->schema([
                                Section::make('Product Images')
                                    ->schema([
                                        FileUpload::make('images')
                                            ->label(__('products.fields.images'))
                                            ->image()
                                            ->multiple()
                                            ->directory('products')
                                            ->visibility('public')
                                            ->reorderable()
                                            ->appendFiles(),
                                    ]),
                                Section::make('SEO Settings')
                                    ->schema([
                                        TextInput::make('seo_title')
                                            ->label(__('products.fields.seo_title'))
                                            ->maxLength(255),
                                        Textarea::make('seo_description')
                                            ->label(__('products.fields.seo_description'))
                                            ->rows(3)
                                            ->maxLength(160),
                                    ]),
                            ]),
                        Tab::make('Settings & Options')
                            ->schema([
                                Section::make('Product Settings')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('brand_id')
                                                    ->label(__('products.fields.brand'))
                                                    ->relationship('brand', 'name')
                                                    ->searchable()
                                                    ->preload(),
                                                Select::make('status')
                                                    ->label(__('products.fields.status'))
                                                    ->options([
                                                        'draft' => __('products.status.draft'),
                                                        'published' => __('products.status.published'),
                                                        'archived' => __('products.status.archived'),
                                                    ])
                                                    ->default('draft'),
                                            ]),
                                        Grid::make(3)
                                            ->schema([
                                                Toggle::make('is_visible')
                                                    ->label(__('products.fields.is_visible'))
                                                    ->default(true),
                                                Toggle::make('is_featured')
                                                    ->label(__('products.fields.is_featured')),
                                                Toggle::make('allow_backorder')
                                                    ->label(__('products.fields.allow_backorder')),
                                            ]),
                                        DateTimePicker::make('published_at')
                                            ->label(__('products.fields.published_at'))
                                            ->default(now()),
                                    ]),
                                Section::make('Physical Properties')
                                    ->schema([
                                        Grid::make(4)
                                            ->schema([
                                                TextInput::make('weight')
                                                    ->label(__('products.fields.weight'))
                                                    ->numeric()
                                                    ->suffix('kg')
                                                    ->step(0.01),
                                                TextInput::make('length')
                                                    ->label(__('products.fields.length'))
                                                    ->numeric()
                                                    ->suffix('cm')
                                                    ->step(0.01),
                                                TextInput::make('width')
                                                    ->label(__('products.fields.width'))
                                                    ->numeric()
                                                    ->suffix('cm')
                                                    ->step(0.01),
                                                TextInput::make('height')
                                                    ->label(__('products.fields.height'))
                                                    ->numeric()
                                                    ->suffix('cm')
                                                    ->step(0.01),
                                            ]),
                                    ]),
                            ]),
                        Tab::make('Advanced')
                            ->schema([
                                Section::make('Additional Data')
                                    ->schema([
                                        KeyValue::make('metadata')
                                            ->label(__('products.fields.metadata'))
                                            ->keyLabel(__('products.fields.metadata_key'))
                                            ->valueLabel(__('products.fields.metadata_value')),
                                        TagsInput::make('tags')
                                            ->label(__('products.fields.tags'))
                                            ->placeholder(__('products.fields.tags_placeholder')),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('main_image')
                    ->label(__('products.fields.image'))
                    ->circular()
                    ->size(50),
                TextColumn::make('name')
                    ->label(__('products.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('sku')
                    ->label(__('products.fields.sku'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('brand.name')
                    ->label(__('products.fields.brand'))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('price')
                    ->label(__('products.fields.price'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('stock_quantity')
                    ->label(__('products.fields.stock'))
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 10 => 'warning',
                        default => 'success',
                    }),
                BadgeColumn::make('status')
                    ->label(__('products.fields.status'))
                    ->colors([
                        'draft' => 'gray',
                        'published' => 'success',
                        'archived' => 'warning',
                    ]),
                IconColumn::make('is_visible')
                    ->label(__('products.fields.is_visible'))
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->label(__('products.fields.is_featured'))
                    ->boolean(),
                TextColumn::make('compare_price')
                    ->label(__('products.fields.compare_price'))
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cost_price')
                    ->label(__('products.fields.cost_price'))
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('weight')
                    ->label(__('products.fields.weight'))
                    ->suffix(' kg')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('reviews_count')
                    ->label(__('products.fields.reviews_count'))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('average_rating')
                    ->label(__('products.fields.average_rating'))
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 1).' ⭐' : 'No ratings')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('published_at')
                    ->label(__('products.fields.published_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('products.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('brand')
                    ->label(__('products.filters.brand'))
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label(__('products.filters.status'))
                    ->options([
                        'draft' => __('products.status.draft'),
                        'published' => __('products.status.published'),
                        'archived' => __('products.status.archived'),
                    ]),
                TernaryFilter::make('is_visible')
                    ->label(__('products.fields.is_visible')),
                TernaryFilter::make('is_featured')
                    ->label(__('products.fields.is_featured')),
                TernaryFilter::make('manage_stock')
                    ->label(__('products.fields.manage_stock')),
                TernaryFilter::make('track_stock')
                    ->label(__('products.fields.track_stock')),
                TernaryFilter::make('allow_backorder')
                    ->label(__('products.fields.allow_backorder')),
                Filter::make('price_range')
                    ->label(__('products.filters.price_range'))
                    ->form([
                        TextInput::make('price_from')
                            ->label(__('products.filters.price_from'))
                            ->numeric()
                            ->prefix('€'),
                        TextInput::make('price_to')
                            ->label(__('products.filters.price_to'))
                            ->numeric()
                            ->prefix('€'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['price_from'],
                                fn (Builder $query, $price): Builder => $query->where('price', '>=', $price),
                            )
                            ->when(
                                $data['price_to'],
                                fn (Builder $query, $price): Builder => $query->where('price', '<=', $price),
                            );
                    }),
                Filter::make('weight_range')
                    ->label(__('products.filters.weight_range'))
                    ->form([
                        TextInput::make('weight_from')
                            ->label(__('products.filters.weight_from'))
                            ->numeric()
                            ->suffix('kg'),
                        TextInput::make('weight_to')
                            ->label(__('products.filters.weight_to'))
                            ->numeric()
                            ->suffix('kg'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['weight_from'],
                                fn (Builder $query, $weight): Builder => $query->where('weight', '>=', $weight),
                            )
                            ->when(
                                $data['weight_to'],
                                fn (Builder $query, $weight): Builder => $query->where('weight', '<=', $weight),
                            );
                    }),
                Filter::make('low_stock')
                    ->label(__('products.filters.low_stock'))
                    ->query(fn (Builder $query): Builder => $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold')),
                Filter::make('out_of_stock')
                    ->label(__('products.filters.out_of_stock'))
                    ->query(fn (Builder $query): Builder => $query->where('stock_quantity', '<=', 0)),
                Filter::make('created_at')
                    ->form([
                        DateTimePicker::make('created_from')
                            ->label(__('products.filters.created_from')),
                        DateTimePicker::make('created_until')
                            ->label(__('products.filters.created_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('publish')
                        ->label(__('products.actions.publish'))
                        ->icon('heroicon-o-eye')
                        ->action(function (Collection $records) {
                            $records->each->update(['status' => 'published', 'is_visible' => true]);
                            Notification::make()
                                ->title(__('products.notifications.published'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('unpublish')
                        ->label(__('products.actions.unpublish'))
                        ->icon('heroicon-o-eye-slash')
                        ->action(function (Collection $records) {
                            $records->each->update(['status' => 'draft', 'is_visible' => false]);
                            Notification::make()
                                ->title(__('products.notifications.unpublished'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('feature')
                        ->label(__('products.actions.feature'))
                        ->icon('heroicon-o-star')
                        ->action(function (Collection $records) {
                            $records->each->update(['is_featured' => true]);
                            Notification::make()
                                ->title(__('products.notifications.featured'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('unfeature')
                        ->label(__('products.actions.unfeature'))
                        ->icon('heroicon-o-star')
                        ->action(function (Collection $records) {
                            $records->each->update(['is_featured' => false]);
                            Notification::make()
                                ->title(__('products.notifications.unfeatured'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('update_stock')
                        ->label(__('products.actions.update_stock'))
                        ->icon('heroicon-o-cube')
                        ->form([
                            TextInput::make('stock_quantity')
                                ->label(__('products.fields.stock_quantity'))
                                ->numeric()
                                ->required(),
                            TextInput::make('low_stock_threshold')
                                ->label(__('products.fields.low_stock_threshold'))
                                ->numeric()
                                ->default(5),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each->update([
                                'stock_quantity' => $data['stock_quantity'],
                                'low_stock_threshold' => $data['low_stock_threshold'],
                            ]);
                            Notification::make()
                                ->title(__('products.notifications.stock_updated'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('update_prices')
                        ->label(__('products.actions.update_prices'))
                        ->icon('heroicon-o-currency-euro')
                        ->form([
                            TextInput::make('price_increase_percentage')
                                ->label(__('products.fields.price_increase_percentage'))
                                ->numeric()
                                ->suffix('%')
                                ->helperText(__('products.helpers.price_increase')),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $percentage = $data['price_increase_percentage'] ?? 0;
                            $multiplier = 1 + ($percentage / 100);

                            $records->each(function ($product) use ($multiplier) {
                                $product->update([
                                    'price' => round($product->price * $multiplier, 2),
                                    'compare_price' => $product->compare_price ? round($product->compare_price * $multiplier, 2) : null,
                                    'cost_price' => $product->cost_price ? round($product->cost_price * $multiplier, 2) : null,
                                ]);
                            });

                            Notification::make()
                                ->title(__('products.notifications.prices_updated'))
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            CategoriesRelationManager::class,
            CollectionsRelationManager::class,
            DocumentsRelationManager::class,
            ReviewsRelationManager::class,
            VariantsRelationManager::class,
            AttributesRelationManager::class,
            ImagesRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            \App\Filament\Resources\ProductResource\Widgets\ProductStatsWidget::class,
            \App\Filament\Resources\ProductResource\Widgets\ProductChartWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
