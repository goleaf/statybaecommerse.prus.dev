<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Actions\DocumentAction;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Schemas\Schema;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;
use BackedEnum;
use Illuminate\Database\Eloquent\SoftDeletingScope;

final class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cube';

    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?string $recordTitleAttribute = 'name';

    protected static int $globalSearchResultsLimit = 20;

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'sku', 'brand.name', 'categories.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Brand' => $record->brand?->name,
            'SKU' => $record->sku,
            'Price' => '€' . number_format($record->price, 2),
            'Stock' => $record->stock_quantity,
        ];
    }

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Section::make('Product Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Product::class, 'slug', ignoreRecord: true),
                        Forms\Components\Select::make('brand_id')
                            ->relationship('brand', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn(string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                return Brand::create($data)->getKey();
                            }),
                        Forms\Components\Select::make('categories')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn(string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make('parent_id')
                                    ->relationship('parent', 'name')
                                    ->searchable()
                                    ->preload(),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                return Category::create($data)->getKey();
                            }),
                        Forms\Components\Textarea::make('summary')
                            ->maxLength(500)
                            ->rows(3),
                        Forms\Components\RichEditor::make('description')
                            ->maxLength(65535),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Pricing & Inventory')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01)
                            ->required(),
                        Forms\Components\TextInput::make('compare_price')
                            ->label('Compare at Price')
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01),
                        Forms\Components\TextInput::make('cost_price')
                            ->label('Cost Price')
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01),
                        Forms\Components\TextInput::make('sku')
                            ->label('SKU')
                            ->maxLength(255)
                            ->unique(Product::class, 'sku', ignoreRecord: true),
                        Forms\Components\TextInput::make('barcode')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('stock_quantity')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Forms\Components\TextInput::make('low_stock_threshold')
                            ->numeric()
                            ->default(10)
                            ->minValue(0),
                        Forms\Components\TextInput::make('weight')
                            ->numeric()
                            ->suffix('kg')
                            ->step(0.01),
                    ])
                    ->columns(4),
                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\Toggle::make('track_inventory')
                            ->label('Track Inventory')
                            ->default(true),
                        Forms\Components\Toggle::make('is_visible')
                            ->label('Visible')
                            ->default(true),
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured')
                            ->default(false),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'archived' => 'Archived',
                            ])
                            ->default('draft')
                            ->required(),
                    ])
                    ->columns(4),
                Forms\Components\Section::make('Translations')
                    ->schema([
                        Forms\Components\Repeater::make('translations')
                            ->relationship('translations')
                            ->schema([
                                Forms\Components\Select::make('locale')
                                    ->options([
                                        'en' => 'English',
                                        'lt' => 'Lithuanian',
                                    ])
                                    ->required(),
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('summary')
                                    ->maxLength(500)
                                    ->rows(2),
                                Forms\Components\RichEditor::make('description')
                                    ->maxLength(65535),
                                Forms\Components\TextInput::make('seo_title')
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('seo_description')
                                    ->maxLength(300)
                                    ->rows(2),
                            ])
                            ->columns(2)
                            ->defaultItems(2)
                            ->addActionLabel('Add Translation')
                            ->reorderableWithButtons()
                            ->collapsible(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->defaultImageUrl('/images/placeholder-product.jpg')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('sku')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('brand.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('categories.name')
                    ->badge()
                    ->separator(',')
                    ->limit(2)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->numeric()
                    ->sortable()
                    ->color(fn($state): string => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 10 => 'warning',
                        default => 'success',
                    }),
                Tables\Columns\IconColumn::make('is_visible')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'draft' => 'warning',
                        'inactive' => 'danger',
                        'archived' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('brand_id')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'archived' => 'Archived',
                    ]),
                Tables\Filters\Filter::make('low_stock')
                    ->query(fn(Builder $query): Builder => $query->lowStock())
                    ->label('Low Stock'),
                Tables\Filters\Filter::make('featured')
                    ->query(fn(Builder $query): Builder => $query->where('is_featured', true))
                    ->label('Featured Only'),
            ])
            ->actions([
                DocumentAction::make()
                    ->variables(fn(Product $record) => [
                        '$PRODUCT_NAME' => $record->name,
                        '$PRODUCT_SKU' => $record->sku,
                        '$PRODUCT_PRICE' => number_format($record->price, 2) . ' EUR',
                        '$PRODUCT_DESCRIPTION' => $record->description,
                        '$PRODUCT_BRAND' => $record->brand?->name ?? '',
                        '$PRODUCT_CATEGORY' => $record->category?->name ?? '',
                        '$PRODUCT_WEIGHT' => $record->weight ? $record->weight . ' kg' : '',
                    ]),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\VariantsRelationManager::class,
            RelationManagers\ReviewsRelationManager::class,
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
}
