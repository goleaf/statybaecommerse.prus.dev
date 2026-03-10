<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProductImageResource\Pages as ImagePages;
use App\Models\ProductImage;
use App\Services\ProductImages\ProductImageWriteService;
use App\Support\Filament\Forms\Components\SortOrderInput;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use UnitEnum;

final class ProductImageResource extends BaseResource
{
    protected static ?string $model = ProductImage::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $activeNavigationItem = ProductResource::class;

    protected static string|UnitEnum|null $navigationGroup = null;

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.product_images');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.navigation.product_images');
    }

    public static function getModelLabel(): string
    {
        return __('admin.navigation.product_image');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make(__('messages.media'))
                    ->description(__('admin.navigation.product_images'))
                    ->schema([
                        FileUpload::make('path')
                            ->label(__('messages.image'))
                            ->required()
                            ->image()
                            ->disk('public')
                            ->directory('product-images')
                            ->visibility('public')
                            ->maxSize(5120)
                            ->columnSpanFull(),
                    ])
                    ->columnSpan(1),
                Section::make(__('messages.details'))
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                Select::make('product_id')
                                    ->label(__('messages.product'))
                                    ->relationship(
                                        name: 'product',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: static fn (Builder $query): Builder => $query->withoutGlobalScopes(),
                                    )
                                    ->required()
                                    ->searchable()
                                    ->preload(),
                                TextInput::make('alt_text')
                                    ->label(__('messages.description'))
                                    ->maxLength(255)
                                    ->placeholder(__('admin.labels.image_alt_placeholder')),
                                SortOrderInput::make(),
                                Toggle::make('is_default')
                                    ->label(__('admin.navigation.product_image'))
                                    ->helperText(__('admin.labels.primary_image_help')),
                                Toggle::make('is_active')
                                    ->label(__('messages.active'))
                                    ->default(true),
                            ]),
                    ])
                    ->columnSpan(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('url')
                    ->label(__('admin.labels.preview')),
                TextColumn::make('product.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('alt_text')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('sort_order')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->action(fn (ProductImage $record) => app(ProductImageWriteService::class)->delete($record)),
            ])
            ->bulkActions([
                DeleteBulkAction::make()
                    ->action(fn (EloquentCollection $records) => app(ProductImageWriteService::class)->deleteMany($records)),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\ProductImageResource\RelationManagers\ProductsRelationManager::class,
            \App\Filament\Resources\ProductImageResource\RelationManagers\ProductVariantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ImagePages\ListProductImages::route('/'),
            'create' => ImagePages\CreateProductImage::route('/create'),
            'edit'   => ImagePages\EditProductImage::route('/{record}/edit'),
        ];
    }
}
