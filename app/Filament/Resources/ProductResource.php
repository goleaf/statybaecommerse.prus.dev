<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\Action as TableAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use BackedEnum;
use UnitEnum;

/**
 * ProductResource
 *
 * Filament v4 resource for Product management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|UnitEnum|null $navigationGroup = 'Products';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('products.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return "Products"->label();
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('products.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('products.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make(__('products.tabs.main'))
                ->tabs([
                    Tab::make(__('products.tabs.basic_information'))
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Section::make(__('products.sections.basic_information'))
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('name')
                                                ->label(__('products.fields.name'))
                                                ->required()
                                                ->maxLength(255)
                                                ->columnSpan(1),
                                            TextInput::make('slug')
                                                ->label(__('products.fields.slug'))
                                                ->maxLength(255)
                                                ->unique(ignoreRecord: true)
                                                ->columnSpan(1),
                                        ]),
                                    RichEditor::make('description')
                                        ->label(__('products.fields.description'))
                                        ->columnSpanFull(),
                                    Textarea::make('short_description')
                                        ->label(__('products.fields.short_description'))
                                        ->maxLength(500)
                                        ->rows(3)
                                        ->columnSpanFull(),
                                ])
                                ->columns(1),
                        ]),
                    Tab::make(__('products.tabs.pricing'))
                        ->icon('heroicon-o-currency-euro')
                        ->schema([
                            Section::make(__('products.sections.pricing'))
                                ->schema([
                                    Grid::make(3)
                                        ->schema([
                                            TextInput::make('price')
                                                ->label(__('products.fields.price'))
                                                ->required()
                                                ->numeric()
                                                ->step(0.01)
                                                ->prefix('€')
                                                ->columnSpan(1),
                                            TextInput::make('compare_price')
                                                ->label(__('products.fields.compare_price'))
                                                ->numeric()
                                                ->step(0.01)
                                                ->prefix('€')
                                                ->columnSpan(1),
                                            TextInput::make('cost_price')
                                                ->label(__('products.fields.cost_price'))
                                                ->numeric()
                                                ->step(0.01)
                                                ->prefix('€')
                                                ->columnSpan(1),
                                        ]),
                                ])
                                ->columns(1),
                        ]),
                    Tab::make(__('products.tabs.inventory'))
                        ->icon('heroicon-o-archive-box')
                        ->schema([
                            Section::make(__('products.sections.inventory'))
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('sku')
                                                ->label(__('products.fields.sku'))
                                                ->required()
                                                ->unique(ignoreRecord: true)
                                                ->maxLength(255)
                                                ->columnSpan(1),
                                            TextInput::make('barcode')
                                                ->label(__('products.fields.barcode'))
                                                ->maxLength(255)
                                                ->columnSpan(1),
                                        ]),
                                    Grid::make(3)
                                        ->schema([
                                            Toggle::make('manage_stock')
                                                ->label(__('products.fields.manage_stock'))
                                                ->default(true)
                                                ->columnSpan(1),
                                            TextInput::make('stock_quantity')
                                                ->label(__('products.fields.stock_quantity'))
                                                ->numeric()
                                                ->default(0)
                                                ->columnSpan(1),
                                            TextInput::make('low_stock_threshold')
                                                ->label(__('products.fields.low_stock_threshold'))
                                                ->numeric()
                                                ->default(5)
                                                ->columnSpan(1),
                                        ]),
                                    Grid::make(2)
                                        ->schema([
                                            Toggle::make('allow_backorder')
                                                ->label(__('products.fields.allow_backorder'))
                                                ->columnSpan(1),
                                            Toggle::make('track_stock')
                                                ->label(__('products.fields.track_stock'))
                                                ->default(true)
                                                ->columnSpan(1),
                                        ]),
                                ])
                                ->columns(1),
                        ]),
                    Tab::make(__('products.tabs.organization'))
                        ->icon('heroicon-o-tag')
                        ->schema([
                            Section::make(__('products.sections.organization'))
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Select::make('category_id')
                                                ->label(__('products.fields.category'))
                                                ->relationship('categories', 'name')
                                                ->searchable()
                                                ->preload()
                                                ->columnSpan(1),
                                            Select::make('brand_id')
                                                ->label(__('products.fields.brand'))
                                                ->relationship('brand', 'name')
                                                ->searchable()
                                                ->preload()
                                                ->columnSpan(1),
                                        ]),
                                ])
                                ->columns(1),
                        ]),
                    Tab::make(__('products.tabs.visibility'))
                        ->icon('heroicon-o-eye')
                        ->schema([
                            Section::make(__('products.sections.visibility'))
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Toggle::make('is_visible')
                                                ->label(__('products.fields.is_visible'))
                                                ->default(true)
                                                ->columnSpan(1),
                                            Toggle::make('is_featured')
                                                ->label(__('products.fields.is_featured'))
                                                ->columnSpan(1),
                                        ]),
                                    DateTimePicker::make('published_at')
                                        ->label(__('products.fields.published_at'))
                                        ->default(now()),
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
                ImageColumn::make('main_image')
                    ->label(__('products.fields.image'))
                    ->circular()
                    ->size(50),
                TextColumn::make('name')
                    ->label(__('products.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sku')
                    ->label(__('products.fields.sku'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->label(__('products.fields.price'))
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('stock_quantity')
                    ->label(__('products.fields.stock'))
                    ->numeric()
                    ->sortable(),
                BadgeColumn::make('stock_status')
                    ->label(__('products.fields.stock_status'))
                    ->colors([
                        'success' => 'in_stock',
                        'warning' => 'low_stock',
                        'danger' => 'out_of_stock',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'in_stock' => __('products.stock_status.in_stock'),
                        'low_stock' => __('products.stock_status.low_stock'),
                        'out_of_stock' => __('products.stock_status.out_of_stock'),
                        default => $state,
                    }),
                IconColumn::make('is_visible')
                    ->label(__('products.fields.is_visible'))
                    ->boolean(),
                IconColumn::make('is_featured')
                    ->label(__('products.fields.is_featured'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('products.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('brand_id')
                    ->label(__('products.fields.brand'))
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('category_id')
                    ->label(__('products.fields.category'))
                    ->relationship('categories', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_visible')
                    ->label(__('products.fields.is_visible')),
                TernaryFilter::make('is_featured')
                    ->label(__('products.fields.is_featured')),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('publish')
                        ->label(__('products.actions.publish'))
                        ->icon('heroicon-o-eye')
                        ->action(function (Collection $records) {
                            $records->each->update(['is_visible' => true, 'published_at' => now()]);
                            Notification::make()
                                ->title(__('products.messages.bulk_publish_success'))
                                ->success()
                                ->send();
                        }),
                    BulkAction::make('unpublish')
                        ->label(__('products.actions.unpublish'))
                        ->icon('heroicon-o-eye-slash')
                        ->action(function (Collection $records) {
                            $records->each->update(['is_visible' => false]);
                            Notification::make()
                                ->title(__('products.messages.bulk_unpublish_success'))
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
