<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ProductVariantResource\Pages;
use App\Models\ProductVariant;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class ProductVariantResource extends BaseResource
{
    protected static ?string $model = ProductVariant::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Inventory;

    protected static ?int $navigationSort = 2;

    public static function getNavigationLabel(): string
    {
        return __('admin.product_variants.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.product_variants.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.product_variants.model_label');
    }

    public static function getEloquentQuery(): Builder
    {
        // Admin tooling should see all variants regardless of storefront scopes.
        return parent::getEloquentQuery()->withoutGlobalScopes();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            SchemaSection::make(__('admin.product_variants.basic_information'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            Select::make('product_id')
                                ->label(__('messages.product'))
                                ->relationship('product', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            TextInput::make('name')
                                ->label(__('messages.name'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('sku')
                                ->label(__('messages.sku'))
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(100),
                            TextInput::make('price')
                                ->label(__('messages.price'))
                                ->numeric()
                                ->required(),
                        ]),
                ]),
            SchemaSection::make(__('admin.product_variants.inventory'))
                ->schema([
                    SchemaGrid::make(4)
                        ->schema([
                            TextInput::make('stock_quantity')
                                ->label(__('admin.products.stock_quantity'))
                                ->numeric()
                                ->integer()
                                ->default(0),
                            TextInput::make('reserved_quantity')
                                ->label(__('admin.products.reserved_quantity'))
                                ->numeric()
                                ->integer()
                                ->default(0),
                            TextInput::make('low_stock_threshold')
                                ->label(__('admin.products.low_stock_threshold'))
                                ->numeric()
                                ->integer()
                                ->default(0),
                            Toggle::make('track_inventory')
                                ->label(__('admin.products.track_stock'))
                                ->default(true),
                        ]),
                ]),
            SchemaSection::make(__('admin.product_variants.flags'))
                ->schema([
                    SchemaGrid::make(3)
                        ->schema([
                            Toggle::make('is_default')
                                ->label(__('admin.products.is_default_variant'))
                                ->default(false),
                            Toggle::make('is_enabled')
                                ->label(__('admin.products.is_enabled'))
                                ->default(true),
                            Toggle::make('is_featured')
                                ->label(__('admin.products.is_featured'))
                                ->default(false),
                        ]),
                ])
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        $availableExpression = 'COALESCE(available_quantity, stock_quantity - COALESCE(reserved_quantity, 0))';

        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label(__('messages.product'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sku')
                    ->label(__('messages.sku'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->label(__('messages.price'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('available_quantity')
                    ->label(__('admin.products.available_quantity'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('low_stock_threshold')
                    ->label(__('admin.products.low_stock_threshold'))
                    ->numeric()
                    ->sortable(),
                IconColumn::make('track_inventory')
                    ->label(__('admin.products.track_stock'))
                    ->boolean(),
            ])
            ->filters([
                Filter::make('product_lookup')
                    ->label(__('admin.product_variants.product_lookup'))
                    ->form([
                        TextInput::make('product_name')
                            ->label(__('messages.product'))
                            ->placeholder(__('admin.product_variants.product_name_placeholder')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $search = trim((string) ($data['product_name'] ?? ''));

                        if ($search === '') {
                            return $query;
                        }

                        return $query->where(function (Builder $builder) use ($search): void {
                            $builder
                                ->where('name', 'like', "%{$search}%")
                                ->orWhereHas('product', function (Builder $productQuery) use ($search): void {
                                    $productQuery->where('name', 'like', "%{$search}%");
                                });
                        });
                    }),
                SelectFilter::make('stock_status')
                    ->label(__('admin.product_variants.stock_status'))
                    ->options([
                        'in_stock' => __('admin.products.stock_in_stock'),
                        'low_stock' => __('admin.products.stock_low_stock'),
                        'out_of_stock' => __('admin.products.stock_out_of_stock'),
                        'not_tracked' => __('admin.products.stock_not_tracked'),
                    ])
                    ->query(function (Builder $query, array $data) use ($availableExpression): Builder {
                        $state = $data['value'] ?? null;

                        if (! is_string($state) || $state === '') {
                            return $query;
                        }

                        return match ($state) {
                            'in_stock' => $query
                                ->where('track_inventory', true)
                                ->whereRaw("{$availableExpression} > COALESCE(low_stock_threshold, 0)"),
                            'low_stock' => $query
                                ->where('track_inventory', true)
                                ->whereRaw("{$availableExpression} > 0")
                                ->whereRaw("{$availableExpression} <= COALESCE(low_stock_threshold, 0)"),
                            'out_of_stock' => $query
                                ->where('track_inventory', true)
                                ->whereRaw("{$availableExpression} <= 0"),
                            'not_tracked' => $query->where('track_inventory', false),
                            default => $query,
                        };
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
            ->defaultSort('created_at', 'desc');
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
