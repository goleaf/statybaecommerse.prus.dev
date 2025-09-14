<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Schemas\Components\DateTimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\TagsInput;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;


use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use App\Enums\NavigationGroup;
use UnitEnum;

/**
 * Product Resource
 * 
 * Filament resource for managing products in the e-commerce system.
 * Provides comprehensive CRUD operations, filtering, searching, and
 * bulk actions for product management.
 * 
 * @property-read string $model The model class this resource manages
 * @property-read string $navigationIcon The navigation icon for this resource
 * @property-read string|null $navigationGroup The navigation group this resource belongs to
 */
class ProductResource extends Resource
{
    /**
     * The model that this resource corresponds to.
     * 
     * @var string
     */
    protected static ?string $model = Product::class;

    /**
     * The navigation icon for this resource.
     * 
     * @var string|\BackedEnum|null
     */
    /** @var string|\BackedEnum|null */
    protected static $navigationIcon = 'heroicon-o-cube';

    /**
     * Get the navigation group for this resource.
     * 
     * @return string|null The navigation group name
     */
    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::Products->label();
    }

    /**
     * The sort order for this resource in navigation.
     * 
     * @var int|null
     */
    protected static ?int $navigationSort = 1;

    /**
     * The attribute to use as the record title.
     * 
     * @var string|null
     */
    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Configure the form schema for creating and editing products.
     * 
     * Defines the form fields and validation rules for product management,
     * including basic information, pricing, inventory, SEO, and media sections.
     * 
     * @param \Filament\Forms\Form $form The form instance
     * @return \Filament\Forms\Form The configured form
     */
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('translations.product_basic_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('translations.product_name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                                TextInput::make('slug')
                                    ->label(__('translations.product_slug'))
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Product::class, 'slug', ignoreRecord: true)
                                    ->rules(['alpha_dash']),
                            ]),
                        Textarea::make('short_description')
                            ->label(__('translations.product_short_description'))
                            ->maxLength(500)
                            ->rows(3),
                        Textarea::make('description')
                            ->label(__('translations.product_description'))
                            ->rows(5),
                    ])
                    ->columns(1),
                Section::make(__('translations.product_pricing_inventory'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('sku')
                                    ->label(__('translations.product_sku'))
                                    ->required()
                                    ->unique(Product::class, 'sku', ignoreRecord: true)
                                    ->maxLength(255),
                                TextInput::make('barcode')
                                    ->label(__('translations.product_barcode'))
                                    ->maxLength(255),
                                TextInput::make('price')
                                    ->label(__('translations.product_price'))
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('compare_price')
                                    ->label(__('translations.product_compare_price'))
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01),
                                TextInput::make('cost_price')
                                    ->label(__('translations.product_cost_price'))
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01),
                                TextInput::make('sale_price')
                                    ->label(__('translations.product_sale_price'))
                                    ->numeric()
                                    ->prefix('€')
                                    ->step(0.01),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Toggle::make('manage_stock')
                                    ->label(__('translations.product_manage_stock'))
                                    ->live(),
                                Toggle::make('track_stock')
                                    ->label(__('translations.product_track_stock'))
                                    ->visible(fn (Forms\Get $get) => $get('manage_stock')),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('stock_quantity')
                                    ->label(__('translations.product_stock_quantity'))
                                    ->numeric()
                                    ->default(0)
                                    ->visible(fn (Forms\Get $get) => $get('manage_stock')),
                                TextInput::make('low_stock_threshold')
                                    ->label(__('translations.product_low_stock_threshold'))
                                    ->numeric()
                                    ->default(0)
                                    ->visible(fn (Forms\Get $get) => $get('manage_stock')),
                            ]),
                    ])
                    ->columns(1),
                Section::make(__('translations.product_physical_attributes'))
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextInput::make('weight')
                                    ->label(__('translations.product_weight'))
                                    ->numeric()
                                    ->suffix('kg')
                                    ->step(0.01),
                                TextInput::make('length')
                                    ->label(__('translations.product_length'))
                                    ->numeric()
                                    ->suffix('cm')
                                    ->step(0.01),
                                TextInput::make('width')
                                    ->label(__('translations.product_width'))
                                    ->numeric()
                                    ->suffix('cm')
                                    ->step(0.01),
                                TextInput::make('height')
                                    ->label(__('translations.product_height'))
                                    ->numeric()
                                    ->suffix('cm')
                                    ->step(0.01),
                            ]),
                    ])
                    ->columns(1),
                Section::make(__('translations.product_categorization'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('brand_id')
                                    ->label(__('translations.product_brand'))
                                    ->relationship('brand', 'name')
                                    ->searchable()
                                    ->preload(),
                                Select::make('type')
                                    ->label(__('translations.product_type'))
                                    ->options([
                                        'simple' => __('translations.product_type_simple'),
                                        'variable' => __('translations.product_type_variable'),
                                    ])
                                    ->default('simple')
                                    ->required(),
                            ]),
                        Select::make('categories')
                            ->label(__('translations.product_categories'))
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),
                        Select::make('collections')
                            ->label(__('translations.product_collections'))
                            ->relationship('collections', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(1),
                Section::make(__('translations.product_visibility_status'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_visible')
                                    ->label(__('translations.product_is_visible'))
                                    ->default(true),
                                Toggle::make('is_featured')
                                    ->label(__('translations.product_is_featured')),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('status')
                                    ->label(__('translations.product_status'))
                                    ->options([
                                        'draft' => __('translations.product_status_draft'),
                                        'published' => __('translations.product_status_published'),
                                        'archived' => __('translations.product_status_archived'),
                                    ])
                                    ->default('draft')
                                    ->required(),
                                DateTimePicker::make('published_at')
                                    ->label(__('translations.product_published_at'))
                                    ->default(now()),
                            ]),
                        TextInput::make('sort_order')
                            ->label(__('translations.product_sort_order'))
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(1),
                Section::make(__('translations.product_seo'))
                    ->schema([
                        TextInput::make('seo_title')
                            ->label(__('translations.product_seo_title'))
                            ->maxLength(255),
                        Textarea::make('seo_description')
                            ->label(__('translations.product_seo_description'))
                            ->maxLength(500)
                            ->rows(3),
                        TagsInput::make('meta_keywords')
                            ->label(__('translations.product_meta_keywords')),
                    ])
                    ->columns(1),
                Section::make(__('translations.product_additional_settings'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('tax_class')
                                    ->label(__('translations.product_tax_class'))
                                    ->maxLength(255),
                                TextInput::make('shipping_class')
                                    ->label(__('translations.product_shipping_class'))
                                    ->maxLength(255),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('download_limit')
                                    ->label(__('translations.product_download_limit'))
                                    ->numeric(),
                                TextInput::make('download_expiry')
                                    ->label(__('translations.product_download_expiry'))
                                    ->numeric()
                                    ->suffix(__('translations.days')),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('external_url')
                                    ->label(__('translations.product_external_url'))
                                    ->url()
                                    ->maxLength(255),
                                TextInput::make('button_text')
                                    ->label(__('translations.product_button_text'))
                                    ->maxLength(255),
                            ]),
                        TextInput::make('video_url')
                            ->label(__('translations.product_video_url'))
                            ->url()
                            ->maxLength(255),
                        KeyValue::make('metadata')
                            ->label(__('translations.product_metadata'))
                            ->keyLabel(__('translations.key'))
                            ->valueLabel(__('translations.value')),
                    ])
                    ->columns(1),
            ]);
    }

    /**
     * Configure the table schema for listing products.
     * 
     * Defines the table columns, filters, actions, and bulk actions
     * for the product listing page in the admin panel.
     * 
     * @param \Filament\Tables\Table $table The table instance
     * @return \Filament\Tables\Table The configured table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('main_image')
                    ->label(__('translations.product_image'))
                    ->circular()
                    ->size(50)
                    ->defaultImageUrl('/images/placeholder-product.png'),
                TextColumn::make('name')
                    ->label(__('translations.product_name'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('sku')
                    ->label(__('translations.product_sku'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('brand.name')
                    ->label(__('translations.product_brand'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->label(__('translations.product_price'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('sale_price')
                    ->label(__('translations.product_sale_price'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('stock_quantity')
                    ->label(__('translations.product_stock'))
                    ->numeric()
                    ->sortable()
                    ->color(fn (Product $record) => match (true) {
                        $record->stock_quantity <= 0 => 'danger',
                        $record->stock_quantity <= $record->low_stock_threshold => 'warning',
                        default => 'success',
                    }),
                BadgeColumn::make('status')
                    ->label(__('translations.product_status'))
                    ->colors([
                        'secondary' => 'draft',
                        'success' => 'published',
                        'danger' => 'archived',
                    ]),
                IconColumn::make('is_visible')
                    ->label(__('translations.product_is_visible'))
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->label(__('translations.product_is_featured'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('translations.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('translations.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('translations.product_status'))
                    ->options([
                        'draft' => __('translations.product_status_draft'),
                        'published' => __('translations.product_status_published'),
                        'archived' => __('translations.product_status_archived'),
                    ]),
                SelectFilter::make('brand_id')
                    ->label(__('translations.product_brand'))
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('type')
                    ->label(__('translations.product_type'))
                    ->options([
                        'simple' => __('translations.product_type_simple'),
                        'variable' => __('translations.product_type_variable'),
                    ]),
                TernaryFilter::make('is_visible')
                    ->label(__('translations.product_is_visible')),
                TernaryFilter::make('is_featured')
                    ->label(__('translations.product_is_featured')),
                TernaryFilter::make('manage_stock')
                    ->label(__('translations.product_manage_stock')),
                Filter::make('low_stock')
                    ->label(__('translations.product_low_stock'))
                    ->query(fn (Builder $query): Builder => $query->whereRaw('stock_quantity <= low_stock_threshold')),
                Filter::make('out_of_stock')
                    ->label(__('translations.product_out_of_stock'))
                    ->query(fn (Builder $query): Builder => $query->where('stock_quantity', '<=', 0)),
                Filter::make('published')
                    ->label(__('translations.product_published'))
                    ->query(fn (Builder $query): Builder => $query
                        ->where('is_visible', true)
                        ->where('status', 'published')
                        ->whereNotNull('published_at')
                        ->where('published_at', '<=', now())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                TableAction::make('duplicate')
                    ->label(__('translations.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->requiresConfirmation()
                    ->modalHeading(__('translations.confirm_duplicate_product'))
                    ->modalDescription(__('translations.confirm_duplicate_product_description'))
                    ->action(function (Product $record) {
                        $newProduct = $record->replicate();
                        $newProduct->name = $record->name.' (Copy)';
                        $newProduct->sku = $record->sku.'-copy';
                        $newProduct->slug = $record->slug.'-copy';
                        $newProduct->status = 'draft';
                        $newProduct->save();

                        // Copy relationships
                        $newProduct->categories()->sync($record->categories->pluck('id'));
                        $newProduct->collections()->sync($record->collections->pluck('id'));
                        $newProduct->attributes()->sync($record->attributes->pluck('id'));

                        return redirect()->to(static::getUrl('edit', ['record' => $newProduct]));
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('publish')
                        ->label(__('translations.publish'))
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->modalHeading(__('translations.confirm_publish_products'))
                        ->modalDescription(__('translations.confirm_publish_products_description'))
                        ->action(fn ($records) => $records->each->update([
                            'status' => 'published',
                            'is_visible' => true,
                            'published_at' => now(),
                        ])),
                    BulkAction::make('unpublish')
                        ->label(__('translations.unpublish'))
                        ->icon('heroicon-o-x-circle')
                        ->requiresConfirmation()
                        ->modalHeading(__('translations.confirm_unpublish_products'))
                        ->modalDescription(__('translations.confirm_unpublish_products_description'))
                        ->action(fn ($records) => $records->each->update([
                            'status' => 'draft',
                            'is_visible' => false,
                        ])),
                    BulkAction::make('feature')
                        ->label(__('translations.feature'))
                        ->icon('heroicon-o-star')
                        ->requiresConfirmation()
                        ->modalHeading(__('translations.confirm_feature_products'))
                        ->modalDescription(__('translations.confirm_feature_products_description'))
                        ->action(fn ($records) => $records->each->update(['is_featured' => true])),
                    BulkAction::make('unfeature')
                        ->label(__('translations.unfeature'))
                        ->icon('heroicon-o-star')
                        ->requiresConfirmation()
                        ->modalHeading(__('translations.confirm_unfeature_products'))
                        ->modalDescription(__('translations.confirm_unfeature_products_description'))
                        ->action(fn ($records) => $records->each->update(['is_featured' => false])),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CategoriesRelationManager::class,
            RelationManagers\CollectionsRelationManager::class,
            RelationManagers\ImagesRelationManager::class,
            RelationManagers\VariantsRelationManager::class,
            RelationManagers\ReviewsRelationManager::class,
            RelationManagers\AttributesRelationManager::class,
            RelationManagers\DocumentsRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\ProductStatsWidget::class,
            \App\Filament\Widgets\ProductChartWidget::class,
            Widgets\ProductPerformanceWidget::class,
            Widgets\ProductInventoryWidget::class,
            Widgets\ProductCategoriesWidget::class,
            Widgets\ProductBrandsWidget::class,
            Widgets\ProductPricingWidget::class,
            Widgets\ProductReviewsWidget::class,
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGlobalSearchResultTitle($record): string
    {
        return $record->name;
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            __('translations.product_sku') => $record->sku,
            __('translations.product_brand') => $record->brand?->name,
            __('translations.product_status') => $record->status,
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'sku', 'description'];
    }
}
