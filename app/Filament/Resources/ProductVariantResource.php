<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Enums\NavigationIcon;
use App\Filament\Resources\ProductVariantResource\Pages;
use App\Models\ProductVariant;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * ProductVariantResource
 *
 * Filament v4 resource for ProductVariant management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class ProductVariantResource extends Resource
{
    protected static ?string $model = ProductVariant::class;

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'display_name';

    public static function getNavigationLabel(): string
    {
        return __('product_variants.title');
    }

    public static function getPluralModelLabel(): string
    {
        return __('product_variants.plural');
    }

    public static function getModelLabel(): string
    {
        return __('product_variants.single');
    }

    public static function getNavigationIcon(): string|\BackedEnum|Htmlable|null
    {
        return 'heroicon-o-squares-2x2';
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return 'Products';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Tabs::make('Variant Information')
                    ->tabs([
                        Tab::make('Basic Information')
                            ->schema([
                                Section::make('Variant Details')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('product_id')
                                                    ->label(__('product_variants.fields.product'))
                                                    ->relationship('product', 'name')
                                                    ->required()
                                                    ->searchable()
                                                    ->preload(),
                                                TextInput::make('sku')
                                                    ->label(__('product_variants.fields.sku'))
                                                    ->required()
                                                    ->unique(ignoreRecord: true)
                                                    ->maxLength(255),
                                            ]),
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label(__('product_variants.fields.name'))
                                                    ->required()
                                                    ->maxLength(255),
                                                TextInput::make('barcode')
                                                    ->label(__('product_variants.fields.barcode'))
                                                    ->maxLength(255),
                                            ]),
                                        Textarea::make('description_lt')
                                            ->label(__('product_variants.fields.description_lt'))
                                            ->rows(3),
                                        Textarea::make('description_en')
                                            ->label(__('product_variants.fields.description_en'))
                                            ->rows(3),
                                    ]),
                                Section::make('Pricing')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('price')
                                                    ->label(__('product_variants.fields.price'))
                                                    ->required()
                                                    ->numeric()
                                                    ->prefix('€')
                                                    ->step(0.01),
                                                TextInput::make('compare_price')
                                                    ->label(__('product_variants.fields.compare_price'))
                                                    ->numeric()
                                                    ->prefix('€')
                                                    ->step(0.01),
                                                TextInput::make('cost_price')
                                                    ->label(__('product_variants.fields.cost_price'))
                                                    ->numeric()
                                                    ->prefix('€')
                                                    ->step(0.01),
                                            ]),
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('wholesale_price')
                                                    ->label(__('product_variants.fields.wholesale_price'))
                                                    ->numeric()
                                                    ->prefix('€')
                                                    ->step(0.01),
                                                TextInput::make('member_price')
                                                    ->label(__('product_variants.fields.member_price'))
                                                    ->numeric()
                                                    ->prefix('€')
                                                    ->step(0.01),
                                                TextInput::make('promotional_price')
                                                    ->label(__('product_variants.fields.promotional_price'))
                                                    ->numeric()
                                                    ->prefix('€')
                                                    ->step(0.01),
                                            ]),
                                    ]),
                            ]),
                        Tab::make('Inventory & Stock')
                            ->schema([
                                Section::make('Stock Management')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Toggle::make('track_inventory')
                                                    ->label(__('product_variants.fields.track_inventory'))
                                                    ->default(true),
                                                Toggle::make('is_enabled')
                                                    ->label(__('product_variants.fields.is_enabled'))
                                                    ->default(true),
                                            ]),
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('stock_quantity')
                                                    ->label(__('product_variants.fields.stock_quantity'))
                                                    ->numeric()
                                                    ->default(0),
                                                TextInput::make('reserved_quantity')
                                                    ->label(__('product_variants.fields.reserved_quantity'))
                                                    ->numeric()
                                                    ->default(0),
                                                TextInput::make('low_stock_threshold')
                                                    ->label(__('product_variants.fields.low_stock_threshold'))
                                                    ->numeric()
                                                    ->default(5),
                                            ]),
                                    ]),
                                Section::make('Physical Properties')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('weight')
                                                    ->label(__('product_variants.fields.weight'))
                                                    ->numeric()
                                                    ->suffix('kg')
                                                    ->step(0.01),
                                                TextInput::make('variant_type')
                                                    ->label(__('product_variants.fields.variant_type'))
                                                    ->maxLength(255),
                                            ]),
                                    ]),
                            ]),
                        Tab::make('Marketing & Features')
                            ->schema([
                                Section::make('Marketing Settings')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Toggle::make('is_featured')
                                                    ->label(__('product_variants.fields.is_featured')),
                                                Toggle::make('is_new')
                                                    ->label(__('product_variants.fields.is_new')),
                                            ]),
                                        Grid::make(2)
                                            ->schema([
                                                Toggle::make('is_bestseller')
                                                    ->label(__('product_variants.fields.is_bestseller')),
                                                Toggle::make('is_on_sale')
                                                    ->label(__('product_variants.fields.is_on_sale')),
                                            ]),
                                        Grid::make(2)
                                            ->schema([
                                                DateTimePicker::make('sale_start_date')
                                                    ->label(__('product_variants.fields.sale_start_date')),
                                                DateTimePicker::make('sale_end_date')
                                                    ->label(__('product_variants.fields.sale_end_date')),
                                            ]),
                                    ]),
                                Section::make('SEO Settings')
                                    ->schema([
                                        TextInput::make('seo_title_lt')
                                            ->label(__('product_variants.fields.seo_title_lt'))
                                            ->maxLength(255),
                                        TextInput::make('seo_title_en')
                                            ->label(__('product_variants.fields.seo_title_en'))
                                            ->maxLength(255),
                                        Textarea::make('seo_description_lt')
                                            ->label(__('product_variants.fields.seo_description_lt'))
                                            ->rows(3),
                                        Textarea::make('seo_description_en')
                                            ->label(__('product_variants.fields.seo_description_en'))
                                            ->rows(3),
                                    ]),
                            ]),
                        Tab::make('Attributes & Variants')
                            ->schema([
                                Section::make('Variant Attributes')
                                    ->schema([
                                        Repeater::make('attributes')
                                            ->label(__('product_variants.fields.attributes'))
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label(__('product_variants.fields.attribute_name'))
                                                    ->required(),
                                                TextInput::make('value')
                                                    ->label(__('product_variants.fields.attribute_value'))
                                                    ->required(),
                                            ])
                                            ->columns(2),
                                    ]),
                                Section::make('Additional Data')
                                    ->schema([
                                        KeyValue::make('metadata')
                                            ->label(__('product_variants.fields.metadata'))
                                            ->keyLabel(__('product_variants.fields.metadata_key'))
                                            ->valueLabel(__('product_variants.fields.metadata_value')),
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
                ImageColumn::make('primary_image')
                    ->label(__('product_variants.fields.image'))
                    ->circular()
                    ->size(50),
                TextColumn::make('product.name')
                    ->label(__('product_variants.fields.product'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label(__('product_variants.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sku')
                    ->label(__('product_variants.fields.sku'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('price')
                    ->label(__('product_variants.fields.price'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('stock_quantity')
                    ->label(__('product_variants.fields.stock'))
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 10 => 'warning',
                        default => 'success',
                    }),
                BadgeColumn::make('stock_status')
                    ->label(__('product_variants.fields.stock_status'))
                    ->formatStateUsing(fn ($state) => match ($state) {
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
                    ->boolean(),
                IconColumn::make('is_on_sale')
                    ->label(__('product_variants.fields.is_on_sale'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('product_variants.fields.created_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->label(__('product_variants.filters.product'))
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
                    ->label(__('product_variants.filters.stock_status'))
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
                    ->visible(fn (ProductVariant $record): bool => ! $record->is_default_variant),
                ViewAction::make(),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductVariants::route('/'),
            'create' => Pages\CreateProductVariant::route('/create'),
            'view' => Pages\ViewProductVariant::route('/{record}'),
            'edit' => Pages\EditProductVariant::route('/{record}/edit'),
        ];
    }
}
