<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\ProductImageResource\Pages;
use App\Models\ProductImage;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid as SchemaGrid;
use Filament\Schemas\Components\Section as SchemaSection;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

final class ProductImageResource extends BaseResource
{
    protected static ?string $model = ProductImage::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Inventory;

    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('admin.product_images.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.product_images.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.product_images.model_label');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            SchemaSection::make(__('admin.product_images.basic_information'))
                ->schema([
                    SchemaGrid::make(2)
                        ->schema([
                            Select::make('product_id')
                                ->label(__('messages.product'))
                                ->relationship('product', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            FileUpload::make('path')
                                ->label(__('messages.image'))
                                ->disk('public')
                                ->directory('products')
                                ->image()
                                ->dehydrateStateUsing(static function ($state): ?string {
                                    if (is_array($state)) {
                                        $first = $state[0] ?? null;

                                        return is_string($first) ? $first : null;
                                    }

                                    return is_string($state) ? $state : null;
                                })
                                ->required(),
                            TextInput::make('alt_text')
                                ->label(__('admin.products.alt_text'))
                                ->maxLength(255),
                            TextInput::make('sort_order')
                                ->label(__('admin.products.sort_order'))
                                ->numeric()
                                ->integer()
                                ->default(0),
                            Toggle::make('is_active')
                                ->label(__('admin.products.is_active'))
                                ->default(true),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('path')
                    ->label(__('messages.image'))
                    ->disk('public')
                    ->square(),
                TextColumn::make('product.name')
                    ->label(__('messages.product'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('alt_text')
                    ->label(__('admin.products.alt_text'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label(__('admin.products.sort_order'))
                    ->numeric()
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label(__('admin.products.is_active')),
                TextColumn::make('created_at')
                    ->label(__('admin.products.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->label(__('messages.product'))
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
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
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductImages::route('/'),
            'create' => Pages\CreateProductImage::route('/create'),
            'view' => Pages\ViewProductImage::route('/{record}'),
            'edit' => Pages\EditProductImage::route('/{record}/edit'),
        ];
    }
}
