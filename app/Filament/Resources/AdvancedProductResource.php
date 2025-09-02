<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Actions\DocumentAction;
use App\Filament\Resources\AdvancedProductResource\Pages;
use App\Filament\Resources\AdvancedProductResource\RelationManagers;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Attribute;
use App\Services\MultiLanguageTabService;
use Filament\Schemas\Schema;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;
use BackedEnum;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use SolutionForest\TabLayoutPlugin\Components\Tabs;
use SolutionForest\TabLayoutPlugin\Components\Tabs\Tab as TabLayoutTab;

final class AdvancedProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cube';

    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Advanced Products';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Basic Product Information
                Forms\Components\Section::make(__('Product Information'))
                    ->components([
                        Forms\Components\Grid::make(2)
                            ->components([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('Product Name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $state, Forms\Set $set) {
                                        $set('slug', \Illuminate\Support\Str::slug($state));
                                    }),
                                    
                                Forms\Components\TextInput::make('slug')
                                    ->label(__('Slug'))
                                    ->required()
                                    ->unique(Product::class, 'slug', ignoreRecord: true)
                                    ->maxLength(255)
                                    ->helperText(__('Auto-generated from name if left empty')),
                            ]),

                        Forms\Components\Grid::make(3)
                            ->components([
                                Forms\Components\TextInput::make('sku')
                                    ->label(__('SKU'))
                                    ->required()
                                    ->unique(Product::class, 'sku', ignoreRecord: true)
                                    ->maxLength(100),
                                    
                                Forms\Components\TextInput::make('barcode')
                                    ->label(__('Barcode'))
                                    ->maxLength(100),
                                    
                                Forms\Components\Select::make('status')
                                    ->label(__('Status'))
                                    ->options([
                                        'draft' => __('Draft'),
                                        'published' => __('Published'),
                                        'archived' => __('Archived'),
                                    ])
                                    ->default('draft')
                                    ->required(),
                            ]),

                        Forms\Components\RichEditor::make('description')
                            ->label(__('Description'))
                            ->toolbarButtons([
                                'bold', 'italic', 'underline', 'strike',
                                'bulletList', 'orderedList', 'blockquote',
                                'link', 'codeBlock', 'h2', 'h3'
                            ])
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('short_description')
                            ->label(__('Short Description'))
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),

                // Pricing & Inventory
                Forms\Components\Section::make(__('Pricing & Inventory'))
                    ->components([
                        Forms\Components\Grid::make(3)
                            ->components([
                                Forms\Components\TextInput::make('price')
                                    ->label(__('Price'))
                                    ->numeric()
                                    ->prefix('€')
                                    ->required()
                                    ->minValue(0)
                                    ->step(0.01),
                                    
                                Forms\Components\TextInput::make('sale_price')
                                    ->label(__('Sale Price'))
                                    ->numeric()
                                    ->prefix('€')
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->lte('price'),
                                    
                                Forms\Components\TextInput::make('cost_price')
                                    ->label(__('Cost Price'))
                                    ->numeric()
                                    ->prefix('€')
                                    ->minValue(0)
                                    ->step(0.01),
                            ]),

                        Forms\Components\Grid::make(4)
                            ->components([
                                Forms\Components\Toggle::make('manage_stock')
                                    ->label(__('Manage Stock'))
                                    ->default(true)
                                    ->live(),
                                    
                                Forms\Components\TextInput::make('stock_quantity')
                                    ->label(__('Stock Quantity'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->visible(fn (Forms\Get $get): bool => $get('manage_stock')),
                                    
                                Forms\Components\TextInput::make('low_stock_threshold')
                                    ->label(__('Low Stock Threshold'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(5)
                                    ->visible(fn (Forms\Get $get): bool => $get('manage_stock')),
                                    
                                Forms\Components\Toggle::make('track_inventory')
                                    ->label(__('Track Inventory'))
                                    ->default(true),
                            ]),
                    ]),

                // Categorization & Relationships
                Forms\Components\Section::make(__('Categorization'))
                    ->components([
                        Forms\Components\Grid::make(2)
                            ->components([
                                Forms\Components\Select::make('brand_id')
                                    ->label(__('Brand'))
                                    ->relationship('brand', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\Textarea::make('description')
                                            ->rows(3),
                                    ]),
                                    
                                Forms\Components\Select::make('categories')
                                    ->label(__('Categories'))
                                    ->relationship('categories', 'name')
                                    ->multiple()
                                    ->searchable()
                                    ->preload(),
                            ]),

                        Forms\Components\Select::make('collections')
                            ->label(__('Collections'))
                            ->relationship('collections', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),
                    ]),

                // Media & Gallery
                Forms\Components\Section::make(__('Media & Gallery'))
                    ->components([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('images')
                            ->label(__('Product Images'))
                            ->collection('product-images')
                            ->multiple()
                            ->reorderable()
                            ->maxFiles(10)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->columnSpanFull(),
                            
                        Forms\Components\SpatieMediaLibraryFileUpload::make('gallery')
                            ->label(__('Product Gallery'))
                            ->collection('product-gallery')
                            ->multiple()
                            ->reorderable()
                            ->maxFiles(20)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->columnSpanFull(),
                            
                        Forms\Components\TextInput::make('video_url')
                            ->label(__('Product Video URL'))
                            ->url()
                            ->maxLength(255)
                            ->helperText(__('YouTube, Vimeo, or direct video URL')),
                    ]),

                // SEO & Meta
                Forms\Components\Section::make(__('SEO & Meta Information'))
                    ->components([
                        Forms\Components\Grid::make(2)
                            ->components([
                                Forms\Components\TextInput::make('meta_title')
                                    ->label(__('Meta Title'))
                                    ->maxLength(60)
                                    ->helperText(__('Optimal length: 50-60 characters')),
                                    
                                Forms\Components\Textarea::make('meta_description')
                                    ->label(__('Meta Description'))
                                    ->rows(3)
                                    ->maxLength(160)
                                    ->helperText(__('Optimal length: 150-160 characters')),
                            ]),

                        Forms\Components\TagsInput::make('meta_keywords')
                            ->label(__('Meta Keywords'))
                            ->suggestions([
                                'building', 'construction', 'tools', 'materials', 
                                'hardware', 'supplies', 'equipment', 'professional'
                            ]),
                    ]),

                // Advanced Settings
                Forms\Components\Section::make(__('Advanced Settings'))
                    ->components([
                        Forms\Components\Grid::make(3)
                            ->components([
                                Forms\Components\Toggle::make('is_visible')
                                    ->label(__('Visible'))
                                    ->default(true),
                                    
                                Forms\Components\Toggle::make('is_featured')
                                    ->label(__('Featured'))
                                    ->default(false),
                                    
                                Forms\Components\Toggle::make('requires_shipping')
                                    ->label(__('Requires Shipping'))
                                    ->default(true),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->components([
                                Forms\Components\DateTimePicker::make('available_from')
                                    ->label(__('Available From'))
                                    ->native(false),
                                    
                                Forms\Components\DateTimePicker::make('available_until')
                                    ->label(__('Available Until'))
                                    ->native(false),
                            ]),

                        Forms\Components\KeyValue::make('metadata')
                            ->label(__('Additional Metadata'))
                            ->keyLabel(__('Property'))
                            ->valueLabel(__('Value'))
                            ->addActionLabel(__('Add property')),

                        Forms\Components\Textarea::make('admin_notes')
                            ->label(__('Admin Notes'))
                            ->rows(3)
                            ->helperText(__('Internal notes visible only to administrators'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('images')
                    ->label(__('Image'))
                    ->collection('product-images')
                    ->conversion('thumb')
                    ->size(60)
                    ->circular(),
                    
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->description(fn (Product $record): string => $record->sku),
                    
                Tables\Columns\TextColumn::make('brand.name')
                    ->label(__('Brand'))
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('Status'))
                    ->colors([
                        'gray' => 'draft',
                        'success' => 'published',
                        'warning' => 'archived',
                    ]),
                    
                Tables\Columns\TextColumn::make('price')
                    ->label(__('Price'))
                    ->money('EUR')
                    ->sortable()
                    ->weight(FontWeight::Bold),
                    
                Tables\Columns\TextColumn::make('sale_price')
                    ->label(__('Sale Price'))
                    ->money('EUR')
                    ->sortable()
                    ->toggleable()
                    ->color('success'),
                    
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label(__('Stock'))
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state > 20 => 'success',
                        $state > 5 => 'warning',
                        default => 'danger',
                    }),
                    
                Tables\Columns\IconColumn::make('is_visible')
                    ->label(__('Visible'))
                    ->boolean()
                    ->toggleable(),
                    
                Tables\Columns\IconColumn::make('is_featured')
                    ->label(__('Featured'))
                    ->boolean()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('brand_id')
                    ->label(__('Brand'))
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\SelectFilter::make('categories')
                    ->label(__('Categories'))
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options([
                        'draft' => __('Draft'),
                        'published' => __('Published'),
                        'archived' => __('Archived'),
                    ]),
                    
                Tables\Filters\TernaryFilter::make('is_visible')
                    ->label(__('Visible')),
                    
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label(__('Featured')),
                    
                Tables\Filters\Filter::make('low_stock')
                    ->label(__('Low Stock'))
                    ->query(fn (Builder $query): Builder => $query->where('stock_quantity', '<=', 5))
                    ->toggle(),
                    
                Tables\Filters\Filter::make('out_of_stock')
                    ->label(__('Out of Stock'))
                    ->query(fn (Builder $query): Builder => $query->where('stock_quantity', '<=', 0))
                    ->toggle(),
                    
                Tables\Filters\Filter::make('on_sale')
                    ->label(__('On Sale'))
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('sale_price'))
                    ->toggle(),
            ])
            ->actions([
                DocumentAction::make()
                    ->variables(fn(Product $record) => [
                        '$PRODUCT_NAME' => $record->name,
                        '$PRODUCT_SKU' => $record->sku,
                        '$PRODUCT_PRICE' => app_money_format($record->price),
                        '$PRODUCT_BRAND' => $record->brand?->name ?? '',
                        '$PRODUCT_CATEGORY' => $record->categories->pluck('name')->implode(', '),
                        '$PRODUCT_STOCK' => $record->stock_quantity,
                        '$PRODUCT_URL' => route('products.show', ['locale' => 'en', 'slug' => $record->slug]),
                    ]),
                    
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ReplicateAction::make()
                        ->excludeAttributes(['sku', 'slug'])
                        ->beforeReplicaSaved(function (Product $replica, array $data): void {
                            $replica->sku = $data['sku'] . '-copy';
                            $replica->slug = $data['slug'] . '-copy';
                            $replica->name = $data['name'] . ' (Copy)';
                        }),
                    Tables\Actions\DeleteAction::make(),
                ]),
                
                Tables\Actions\Action::make('quick_edit_stock')
                    ->label(__('Quick Stock'))
                    ->icon('heroicon-m-cube')
                    ->color('warning')
                    ->form([
                        Forms\Components\TextInput::make('stock_quantity')
                            ->label(__('Stock Quantity'))
                            ->numeric()
                            ->required()
                            ->minValue(0),
                    ])
                    ->fillForm(fn (Product $record): array => [
                        'stock_quantity' => $record->stock_quantity,
                    ])
                    ->action(function (Product $record, array $data): void {
                        $record->update($data);
                        
                        activity()
                            ->performedOn($record)
                            ->withProperties([
                                'old_stock' => $record->getOriginal('stock_quantity'),
                                'new_stock' => $data['stock_quantity'],
                            ])
                            ->log('Stock quantity updated via quick edit');
                    })
                    ->successNotificationTitle(__('Stock updated successfully')),
                    
                Tables\Actions\Action::make('toggle_visibility')
                    ->label(fn (Product $record): string => $record->is_visible ? __('Hide') : __('Show'))
                    ->icon(fn (Product $record): string => $record->is_visible ? 'heroicon-m-eye-slash' : 'heroicon-m-eye')
                    ->color(fn (Product $record): string => $record->is_visible ? 'warning' : 'success')
                    ->action(function (Product $record): void {
                        $record->update(['is_visible' => !$record->is_visible]);
                        
                        activity()
                            ->performedOn($record)
                            ->log($record->is_visible ? 'Product made visible' : 'Product hidden');
                    })
                    ->requiresConfirmation()
                    ->successNotificationTitle(__('Product visibility updated')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('bulk_publish')
                        ->label(__('Publish Selected'))
                        ->icon('heroicon-m-eye')
                        ->color('success')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['status' => 'published', 'is_visible' => true]);
                            }
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                        
                    Tables\Actions\BulkAction::make('bulk_hide')
                        ->label(__('Hide Selected'))
                        ->icon('heroicon-m-eye-slash')
                        ->color('warning')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_visible' => false]);
                            }
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                        
                    Tables\Actions\BulkAction::make('bulk_feature')
                        ->label(__('Feature Selected'))
                        ->icon('heroicon-m-star')
                        ->color('info')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_featured' => true]);
                            }
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->persistSortInSession()
            ->persistSearchInSession()
            ->persistFiltersInSession()
            ->striped()
            ->poll('60s');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make(__('Product Overview'))
                    ->schema([
                        Components\Grid::make(2)
                            ->schema([
                                Components\TextEntry::make('name')
                                    ->label(__('Name'))
                                    ->weight(FontWeight::Bold)
                                    ->size('lg'),
                                    
                                Components\TextEntry::make('sku')
                                    ->label(__('SKU'))
                                    ->copyable()
                                    ->badge(),
                            ]),
                            
                        Components\TextEntry::make('description')
                            ->label(__('Description'))
                            ->html()
                            ->columnSpanFull(),
                    ]),
                    
                Components\Section::make(__('Pricing Information'))
                    ->schema([
                        Components\Grid::make(3)
                            ->schema([
                                Components\TextEntry::make('price')
                                    ->label(__('Price'))
                                    ->money('EUR')
                                    ->weight(FontWeight::Bold),
                                    
                                Components\TextEntry::make('sale_price')
                                    ->label(__('Sale Price'))
                                    ->money('EUR')
                                    ->color('success'),
                                    
                                Components\TextEntry::make('cost_price')
                                    ->label(__('Cost Price'))
                                    ->money('EUR')
                                    ->color('gray'),
                            ]),
                    ]),
                    
                Components\Section::make(__('Inventory & Stock'))
                    ->schema([
                        Components\Grid::make(4)
                            ->schema([
                                Components\IconEntry::make('manage_stock')
                                    ->label(__('Manage Stock'))
                                    ->boolean(),
                                    
                                Components\TextEntry::make('stock_quantity')
                                    ->label(__('Stock'))
                                    ->badge()
                                    ->color(fn (int $state): string => match (true) {
                                        $state > 20 => 'success',
                                        $state > 5 => 'warning',
                                        default => 'danger',
                                    }),
                                    
                                Components\TextEntry::make('low_stock_threshold')
                                    ->label(__('Low Stock Threshold')),
                                    
                                Components\IconEntry::make('track_inventory')
                                    ->label(__('Track Inventory'))
                                    ->boolean(),
                            ]),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\VariantsRelationManager::class,
            RelationManagers\ReviewsRelationManager::class,
            RelationManagers\DocumentsRelationManager::class,
            RelationManagers\AttributesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdvancedProducts::route('/'),
            'create' => Pages\CreateAdvancedProduct::route('/create'),
            'view' => Pages\ViewAdvancedProduct::route('/{record}'),
            'edit' => Pages\EditAdvancedProduct::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()
            ->with(['brand', 'categories']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'sku', 'description', 'brand.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Brand' => $record->brand?->name,
            'SKU' => $record->sku,
            'Price' => app_money_format($record->price),
            'Stock' => $record->stock_quantity,
            'Status' => ucfirst($record->status),
        ];
    }
}
