<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

final class ProductResource extends BaseResource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Inventory;

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return __('admin.products.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.products.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.products.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            SchemaSection::make(__('admin.products.basic_information'))
                ->description(__('admin.products.basic_information_description'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label(__('messages.name'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('sku')
                                ->label(__('messages.sku'))
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(100),
                        ]),
                    RichEditor::make('description')
                        ->label(__('messages.description'))
                        ->columnSpanFull(),
                    SchemaGrid::make(2)
                        ->schema([
                            Select::make('brand_id')
                                ->label(__('messages.brand'))
                                ->relationship('brand', 'name')
                                ->searchable()
                                ->preload(),
                            Select::make('category_id')
                                ->label(__('messages.category'))
                                ->relationship('category', 'name')
                                ->searchable()
                                ->preload(),
                        ]),
                    Toggle::make('is_active')
                        ->label(__('admin.products.is_active'))
                        ->default(true),
                ]),
            SchemaSection::make(__('messages.media'))
                ->description(__('admin.products.images_description'))
                ->schema([
                    FileUpload::make('images')
                        ->label(__('messages.image'))
                        ->multiple()
                        ->image()
                        ->reorderable()
                        ->columnSpanFull(),
                ])
                ->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('images')
                    ->label(__('messages.image'))
                    ->circular()
                    ->stacked()
                    ->limit(3),
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sku')
                    ->label(__('messages.sku'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('brand.name')
                    ->label(__('messages.brand'))
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label(__('messages.category'))
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label(__('admin.products.is_active')),
                TextColumn::make('created_at')
                    ->label(__('admin.products.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view'   => Pages\ViewProduct::route('/{record}'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
