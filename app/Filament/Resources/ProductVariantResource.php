<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProductVariantResource\Pages;
use App\Models\ProductVariant;
use App\Models\Product;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Enums\NavigationGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use BackedEnum;
use UnitEnum;
use Filament\Schemas\Schema;

/**
 * ProductVariantResource
 * 
 * Filament v4 resource for ProductVariant management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class ProductVariantResource extends Resource
{
    protected static ?string $model = ProductVariant::class;
    
    protected static $navigationGroup = 'Products';
    
    protected static ?int $navigationSort = 3;
    protected static ?string $recordTitleAttribute = 'display_name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('product_variants.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return 'Products'->label();
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('product_variants.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('product_variants.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Tabs::make(__('product_variants.tabs.main'))
                ->tabs([
                    Tab::make(__('product_variants.tabs.basic_information'))
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Section::make(__('product_variants.sections.basic_information'))
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Select::make('product_id')
                                                ->label(__('product_variants.fields.product'))
                                                ->relationship('product', 'name')
                                                ->required()
                                                ->searchable()
                                                ->preload()
                                                ->createOptionForm([
                                                    TextInput::make('name')
                                                        ->label(__('products.fields.name'))
                                                        ->required()
                                                        ->maxLength(255),
                                                    TextInput::make('sku')
                                                        ->label(__('products.fields.sku'))
                                                        ->required()
                                                        ->unique('products', 'sku')
                                                        ->maxLength(255),
                                                ])
                                                ->columnSpan(1),

                                            TextInput::make('name')
                                                ->label(__('product_variants.fields.name'))
                                                ->required()
                                                ->maxLength(255)
                                                ->columnSpan(1),
                                        ]),

                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('variant_name_lt')
                                                ->label(__('product_variants.fields.variant_name_lt'))
                                                ->maxLength(255)
                                                ->columnSpan(1),

                                            TextInput::make('variant_name_en')
                                                ->label(__('product_variants.fields.variant_name_en'))
                                                ->maxLength(255)
                                                ->columnSpan(1),
                                        ]),

                                    Grid::make(2)
                                        ->schema([
                                            Textarea::make('description_lt')
                                                ->label(__('product_variants.fields.description_lt'))
                                                ->rows(3)
                                                ->columnSpan(1),

                                            Textarea::make('description_en')
                                                ->label(__('product_variants.fields.description_en'))
                                                ->rows(3)
                                                ->columnSpan(1),
                                        ]),

                                    Grid::make(3)
                                        ->schema([
                                            TextInput::make('sku')
                                                ->label(__('product_variants.fields.sku'))
                                                ->required()
                                                ->unique(ignoreRecord: true)
                                                ->maxLength(255)
                                                ->columnSpan(1),

                                            TextInput::make('variant_sku_suffix')
                                                ->label(__('product_variants.fields.variant_sku_suffix'))
                                                ->maxLength(50)
                                                ->columnSpan(1),

                                            TextInput::make('barcode')
                                                ->label(__('product_variants.fields.barcode'))
                                                ->maxLength(255)
                                                ->columnSpan(1),
                                        ]),

                                    Grid::make(2)
                                        ->schema([
                                            Select::make('variant_type')
                                                ->label(__('product_variants.fields.variant_type'))
                                                ->options([
                                                    'size' => __('product_variants.variant_types.size'),
                                                    'color' => __('product_variants.variant_types.color'),
                                                    'material' => __('product_variants.variant_types.material'),
                                                    'style' => __('product_variants.variant_types.style'),
                                                    'custom' => __('product_variants.variant_types.custom'),
                                                ])
                                                ->default('size')
                                                ->required()
                                                ->columnSpan(1),

                                            Toggle::make('is_default_variant')
                                                ->label(__('product_variants.fields.is_default_variant'))
                                                ->columnSpan(1),
                                        ]),
                                ])
                                ->columns(1),
                        ]),

                    Tab::make(__('product_variants.tabs.size_information'))
                        ->icon('heroicon-o-cube')
                        ->schema([
                            Section::make(__('product_variants.sections.size_information'))
                                ->schema([
                                    Grid::make(3)
                                        ->schema([
                                            TextInput::make('size')
                                                ->label(__('product_variants.fields.size'))
                                                ->maxLength(50)
                                                ->columnSpan(1),

                                            Select::make('size_unit')
                                                ->label(__('product_variants.fields.size_unit'))
                                                ->options([
                                                    'cm' => 'cm',
                                                    'mm' => 'mm',
                                                    'm' => 'm',
                                                    'in' => 'in',
                                                    'ft' => 'ft',
                                                ])
                                                ->default('cm')
                                                ->columnSpan(1),

                                            TextInput::make('size_display')
                                                ->label(__('product_variants.fields.size_display'))
                                                ->maxLength(100)
                                                ->columnSpan(1),
                                        ]),

                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('size_price_modifier')
                                                ->label(__('product_variants.fields.size_price_modifier'))
                                                ->numeric()
                                                ->step(0.01)
                                                ->prefix('€')
                                                ->columnSpan(1),

                                            TextInput::make('size_weight_modifier')
                                                ->label(__('product_variants.fields.size_weight_modifier'))
                                                ->numeric()
                                                ->step(0.001)
                                                ->suffix('kg')
                                                ->columnSpan(1),
                                        ]),
                                ])
                                ->columns(1),
                        ]),

                    Tab::make(__('product_variants.tabs.pricing'))
                        ->icon('heroicon-o-currency-euro')
                        ->schema([
                            Section::make(__('product_variants.sections.pricing'))
                                ->schema([
                                    Grid::make(3)
                                        ->schema([
                                            TextInput::make('price')
                                                ->label(__('product_variants.fields.price'))
                                                ->required()
                                                ->numeric()
                                                ->step(0.01)
                                                ->prefix('€')
                                                ->columnSpan(1),

                                            TextInput::make('compare_price')
                                                ->label(__('product_variants.fields.compare_price'))
                                                ->numeric()
                                                ->step(0.01)
                                                ->prefix('€')
                                                ->columnSpan(1),

                                            TextInput::make('cost_price')
                                                ->label(__('product_variants.fields.cost_price'))
                                                ->numeric()
                                                ->step(0.01)
                                                ->prefix('€')
                                                ->columnSpan(1),
                                        ]),

                                    Grid::make(3)
                                        ->schema([
                                            TextInput::make('wholesale_price')
                                                ->label(__('product_variants.fields.wholesale_price'))
                                                ->numeric()
                                                ->step(0.01)
                                                ->prefix('€')
                                                ->columnSpan(1),

                                            TextInput::make('member_price')
                                                ->label(__('product_variants.fields.member_price'))
                                                ->numeric()
                                                ->step(0.01)
                                                ->prefix('€')
                                                ->columnSpan(1),

                                            TextInput::make('promotional_price')
                                                ->label(__('product_variants.fields.promotional_price'))
                                                ->numeric()
                                                ->step(0.01)
                                                ->prefix('€')
                                                ->columnSpan(1),
                                        ]),

                                    Grid::make(3)
                                        ->schema([
                                            Toggle::make('is_on_sale')
                                                ->label(__('product_variants.fields.is_on_sale'))
                                                ->columnSpan(1),

                                            TextInput::make('sale_start_date')
                                                ->label(__('product_variants.fields.sale_start_date'))
                                                ->dateTime()
                                                ->columnSpan(1),

                                            TextInput::make('sale_end_date')
                                                ->label(__('product_variants.fields.sale_end_date'))
                                                ->dateTime()
                                                ->columnSpan(1),
                                        ]),
                                ])
                                ->columns(1),
                        ]),

                    Tab::make(__('product_variants.tabs.inventory'))
                        ->icon('heroicon-o-archive-box')
                        ->schema([
                            Section::make(__('product_variants.sections.inventory'))
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Toggle::make('track_inventory')
                                                ->label(__('product_variants.fields.track_inventory'))
                                                ->default(true)
                                                ->columnSpan(1),

                                            Toggle::make('allow_backorder')
                                                ->label(__('product_variants.fields.allow_backorder'))
                                                ->columnSpan(1),
                                        ]),

                                    Grid::make(4)
                                        ->schema([
                                            TextInput::make('stock_quantity')
                                                ->label(__('product_variants.fields.stock_quantity'))
                                                ->numeric()
                                                ->default(0)
                                                ->columnSpan(1),

                                            TextInput::make('reserved_quantity')
                                                ->label(__('product_variants.fields.reserved_quantity'))
                                                ->numeric()
                                                ->default(0)
                                                ->columnSpan(1),

                                            TextInput::make('available_quantity')
                                                ->label(__('product_variants.fields.available_quantity'))
                                                ->numeric()
                                                ->default(0)
                                                ->columnSpan(1),

                                            TextInput::make('low_stock_threshold')
                                                ->label(__('product_variants.fields.low_stock_threshold'))
                                                ->numeric()
                                                ->default(5)
                                                ->columnSpan(1),
                                        ]),
                                ])
                                ->columns(1),
                        ]),

                    Tab::make(__('product_variants.tabs.attributes'))
                        ->icon('heroicon-o-tag')
                        ->schema([
                            Section::make(__('product_variants.sections.attributes'))
                                ->schema([
                                    Repeater::make('attributes')
                                        ->relationship()
                                        ->schema([
                                            Select::make('attribute_id')
                                                ->label(__('product_variants.fields.attribute'))
                                                ->relationship('attribute', 'name')
                                                ->required()
                                                ->searchable()
                                                ->preload(),

                                            Select::make('attribute_value_id')
                                                ->label(__('product_variants.fields.attribute_value'))
                                                ->relationship('attribute', 'values')
                                                ->getOptionLabelFromRecordUsing(fn ($record) => $record->value)
                                                ->required()
                                                ->searchable()
                                                ->preload(),
                                        ])
                                        ->columns(2)
                                        ->addActionLabel(__('product_variants.actions.add_attribute'))
                                        ->collapsible(),
                                ])
                                ->columns(1),
                        ]),

                    Tab::make(__('product_variants.tabs.images'))
                        ->icon('heroicon-o-photo')
                        ->schema([
                            Section::make(__('product_variants.sections.images'))
                                ->schema([
                                    Repeater::make('images')
                                        ->relationship()
                                        ->schema([
                                            FileUpload::make('image_path')
                                                ->label(__('product_variants.fields.image'))
                                                ->image()
                                                ->directory('variant-images')
                                                ->required(),

                                            TextInput::make('alt_text')
                                                ->label(__('product_variants.fields.alt_text'))
                                                ->maxLength(255),

                                            TextInput::make('sort_order')
                                                ->label(__('product_variants.fields.sort_order'))
                                                ->numeric()
                                                ->default(0),

                                            Toggle::make('is_primary')
                                                ->label(__('product_variants.fields.is_primary')),
                                        ])
                                        ->columns(2)
                                        ->addActionLabel(__('product_variants.actions.add_image'))
                                        ->collapsible(),
                                ])
                                ->columns(1),
                        ]),

                    Tab::make(__('product_variants.tabs.analytics'))
                        ->icon('heroicon-o-chart-bar')
                        ->schema([
                            Section::make(__('product_variants.sections.analytics'))
                                ->schema([
                                    Grid::make(3)
                                        ->schema([
                                            Toggle::make('is_featured')
                                                ->label(__('product_variants.fields.is_featured'))
                                                ->default(false)
                                                ->columnSpan(1),

                                            Toggle::make('is_new')
                                                ->label(__('product_variants.fields.is_new'))
                                                ->default(false)
                                                ->columnSpan(1),

                                            Toggle::make('is_bestseller')
                                                ->label(__('product_variants.fields.is_bestseller'))
                                                ->default(false)
                                                ->columnSpan(1),
                                        ]),

                                    Grid::make(3)
                                        ->schema([
                                            TextInput::make('views_count')
                                                ->label(__('product_variants.fields.views_count'))
                                                ->numeric()
                                                ->default(0)
                                                ->columnSpan(1),

                                            TextInput::make('clicks_count')
                                                ->label(__('product_variants.fields.clicks_count'))
                                                ->numeric()
                                                ->default(0)
                                                ->columnSpan(1),

                                            TextInput::make('conversion_rate')
                                                ->label(__('product_variants.fields.conversion_rate'))
                                                ->numeric()
                                                ->step(0.01)
                                                ->suffix('%')
                                                ->default(0)
                                                ->columnSpan(1),
                                        ]),
                                ])
                                ->columns(1),
                        ]),

                    Tab::make(__('product_variants.tabs.seo'))
                        ->icon('heroicon-o-magnifying-glass')
                        ->schema([
                            Section::make(__('product_variants.sections.seo'))
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('seo_title_lt')
                                                ->label(__('product_variants.fields.seo_title_lt'))
                                                ->maxLength(255)
                                                ->columnSpan(1),

                                            TextInput::make('seo_title_en')
                                                ->label(__('product_variants.fields.seo_title_en'))
                                                ->maxLength(255)
                                                ->columnSpan(1),
                                        ]),

                                    Grid::make(2)
                                        ->schema([
                                            Textarea::make('seo_description_lt')
                                                ->label(__('product_variants.fields.seo_description_lt'))
                                                ->rows(3)
                                                ->columnSpan(1),

                                            Textarea::make('seo_description_en')
                                                ->label(__('product_variants.fields.seo_description_en'))
                                                ->rows(3)
                                                ->columnSpan(1),
                                        ]),
                                ])
                                ->columns(1),
                        ]),

                    Tab::make(__('product_variants.tabs.settings'))
                        ->icon('heroicon-o-cog-6-tooth')
                        ->schema([
                            Section::make(__('product_variants.sections.settings'))
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Toggle::make('is_enabled')
                                                ->label(__('product_variants.fields.is_enabled'))
                                                ->default(true)
                                                ->columnSpan(1),

                                            TextInput::make('position')
                                                ->label(__('product_variants.fields.position'))
                                                ->numeric()
                                                ->default(0)
                                                ->columnSpan(1),
                                        ]),

                                    KeyValue::make('variant_metadata')
                                        ->label(__('product_variants.fields.variant_metadata'))
                                        ->keyLabel(__('product_variants.fields.metadata_key'))
                                        ->valueLabel(__('product_variants.fields.metadata_value'))
                                        ->addActionLabel(__('product_variants.actions.add_metadata')),
                                ])
                                ->columns(1),
                        ]),
                ])
                ->columnSpanFull(),
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
                ImageColumn::make('primaryImage.image_path')
                    ->label(__('product_variants.fields.image'))
                    ->circular()
                    ->size(50),

                TextColumn::make('product.name')
                    ->label(__('product_variants.fields.product'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label(__('product_variants.fields.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('size_display_name')
                    ->label(__('product_variants.fields.size'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('variant_sku')
                    ->label(__('product_variants.fields.sku'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('final_price')
                    ->label(__('product_variants.fields.price'))
                    ->money('EUR')
                    ->sortable(),

                TextColumn::make('available_quantity')
                    ->label(__('product_variants.fields.stock'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('views_count')
                    ->label(__('product_variants.fields.views_count'))
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('conversion_rate')
                    ->label(__('product_variants.fields.conversion_rate'))
                    ->formatStateUsing(fn (float $state): string => number_format($state, 2) . '%')
                    ->sortable()
                    ->toggleable(),

                BadgeColumn::make('stock_status')
                    ->label(__('product_variants.fields.stock_status'))
                    ->colors([
                        'success' => 'in_stock',
                        'warning' => 'low_stock',
                        'danger' => 'out_of_stock',
                        'secondary' => 'not_tracked',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in_stock' => __('product_variants.stock_status.in_stock'),
                        'low_stock' => __('product_variants.stock_status.low_stock'),
                        'out_of_stock' => __('product_variants.stock_status.out_of_stock'),
                        'not_tracked' => __('product_variants.stock_status.not_tracked'),
                        default => $state,
                    }),

                IconColumn::make('is_enabled')
                    ->label(__('product_variants.fields.is_enabled'))
                    ->boolean(),

                IconColumn::make('is_default_variant')
                    ->label(__('product_variants.fields.is_default_variant'))
                    ->boolean(),

                IconColumn::make('is_featured')
                    ->label(__('product_variants.fields.is_featured'))
                    ->boolean()
                    ->toggleable(),

                IconColumn::make('is_on_sale')
                    ->label(__('product_variants.fields.is_on_sale'))
                    ->boolean()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('product_variants.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->label(__('product_variants.fields.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('variant_type')
                    ->label(__('product_variants.fields.variant_type'))
                    ->options([
                        'size' => __('product_variants.variant_types.size'),
                        'color' => __('product_variants.variant_types.color'),
                        'material' => __('product_variants.variant_types.material'),
                        'style' => __('product_variants.variant_types.style'),
                        'custom' => __('product_variants.variant_types.custom'),
                    ]),

                SelectFilter::make('stock_status')
                    ->label(__('product_variants.fields.stock_status'))
                    ->options([
                        'in_stock' => __('product_variants.stock_status.in_stock'),
                        'low_stock' => __('product_variants.stock_status.low_stock'),
                        'out_of_stock' => __('product_variants.stock_status.out_of_stock'),
                        'not_tracked' => __('product_variants.stock_status.not_tracked'),
                    ]),

                TernaryFilter::make('is_enabled')
                    ->label(__('product_variants.fields.is_enabled')),

                TernaryFilter::make('is_default_variant')
                    ->label(__('product_variants.fields.is_default_variant')),

                TernaryFilter::make('is_featured')
                    ->label(__('product_variants.fields.is_featured')),

                TernaryFilter::make('is_on_sale')
                    ->label(__('product_variants.fields.is_on_sale')),

                TernaryFilter::make('is_new')
                    ->label(__('product_variants.fields.is_new')),

                TernaryFilter::make('is_bestseller')
                    ->label(__('product_variants.fields.is_bestseller')),
            ])
            ->actions([
                Action::make('set_default')
                    ->label(__('product_variants.actions.set_default'))
                    ->icon('heroicon-o-star')
                    ->action(function (ProductVariant $record) {
                        $record->setAsDefault();
                        Notification::make()
                            ->title(__('product_variants.messages.set_as_default_success'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn (ProductVariant $record): bool => !$record->is_default_variant),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('enable')
                        ->label(__('product_variants.actions.enable'))
                        ->icon('heroicon-o-check-circle')
                        ->action(function (Collection $records) {
                            $records->each->update(['is_enabled' => true]);
                            Notification::make()
                                ->title(__('product_variants.messages.bulk_enable_success'))
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('disable')
                        ->label(__('product_variants.actions.disable'))
                        ->icon('heroicon-o-x-circle')
                        ->action(function (Collection $records) {
                            $records->each->update(['is_enabled' => false]);
                            Notification::make()
                                ->title(__('product_variants.messages.bulk_disable_success'))
                                ->success()
                                ->send();
                        }),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Get the resource pages.
     * @return array
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductVariants::route('/'),
            'create' => Pages\CreateProductVariant::route('/create'),
            'edit' => Pages\EditProductVariant::route('/{record}/edit'),
        ];
    }
}
