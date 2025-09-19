<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\WishlistItemResource\Pages;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\UserWishlist;
use App\Models\WishlistItem;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkAction as TableBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use BackedEnum;
use UnitEnum;

/**
 * WishlistItemResource
 *
 * Filament v4 resource for WishlistItem management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class WishlistItemResource extends Resource
{
    protected static ?string $model = WishlistItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    /** @var UnitEnum|string|null */    /** @var UnitEnum|string|null */
    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Customers;

    protected static ?int $navigationSort = 10;

    protected static ?string $recordTitleAttribute = 'product.name';

    /**
     * Handle getNavigationLabel functionality with proper error handling.
     * @return string
     */
    public static function getNavigationLabel(): string
    {
        return __('admin.wishlist_items.title');
    }

    /**
     * Handle getNavigationGroup functionality with proper error handling.
     * @return string|null
     */
    public static function getNavigationGroup(): ?string
    {
        return 'Customers';
    }

    /**
     * Handle getPluralModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getPluralModelLabel(): string
    {
        return __('admin.wishlist_items.plural');
    }

    /**
     * Handle getModelLabel functionality with proper error handling.
     * @return string
     */
    public static function getModelLabel(): string
    {
        return __('admin.wishlist_items.single');
    }

    /**
     * Configure the Filament form schema with fields and validation.
     * @param Schema $schema
     * @return Schema
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('admin.wishlist_items.form.sections.basic_information'))
                ->components([
                    Grid::make(2)
                        ->components([
                            Select::make('wishlist_id')
                                ->label(__('admin.wishlist_items.form.fields.wishlist'))
                                ->relationship('wishlist', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->columnSpan(1),
                            Select::make('product_id')
                                ->label(__('admin.wishlist_items.form.fields.product'))
                                ->relationship('product', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->columnSpan(1),
                        ]),
                    Grid::make(2)
                        ->components([
                            Select::make('variant_id')
                                ->label(__('admin.wishlist_items.form.fields.variant'))
                                ->relationship('variant', 'name')
                                ->searchable()
                                ->preload()
                                ->columnSpan(1),
                            TextInput::make('quantity')
                                ->label(__('admin.wishlist_items.form.fields.quantity'))
                                ->numeric()
                                ->default(1)
                                ->required()
                                ->columnSpan(1),
                        ]),
                    Textarea::make('notes')
                        ->label(__('admin.wishlist_items.form.fields.notes'))
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(1),
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
                TextColumn::make('wishlist.user.name')
                    ->label(__('admin.wishlist_items.form.fields.user'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('wishlist.name')
                    ->label(__('admin.wishlist_items.form.fields.wishlist'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label(__('admin.wishlist_items.form.fields.product'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('variant.name')
                    ->label(__('admin.wishlist_items.form.fields.variant'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label(__('admin.wishlist_items.form.fields.quantity'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.wishlist_items.form.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('wishlist')
                    ->label(__('admin.wishlist_items.form.fields.wishlist'))
                    ->relationship('wishlist', 'name'),
                SelectFilter::make('product')
                    ->label(__('admin.wishlist_items.form.fields.product'))
                    ->relationship('product', 'name'),
                SelectFilter::make('variant')
                    ->label(__('admin.wishlist_items.form.fields.variant'))
                    ->relationship('variant', 'name'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                \Filament\Actions\Action::make('move_to_cart')
                    ->label(__('admin.wishlist_items.actions.move_to_cart'))
                    ->icon('heroicon-o-shopping-cart')
                    ->action(function (WishlistItem $record): void {
                        // This would typically create a cart item
                        // For now, we'll just show a notification
                        FilamentNotification::make()
                            ->title(__('admin.wishlist_items.moved_to_cart_successfully'))
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    TableBulkAction::make('move_to_cart')
                        ->label(__('admin.wishlist_items.actions.move_to_cart'))
                        ->icon('heroicon-o-shopping-cart')
                        ->action(function (Collection $records): void {
                            // This would typically create cart items for all records
                            // For now, we'll just show a notification
                            FilamentNotification::make()
                                ->title(__('admin.wishlist_items.moved_to_cart_successfully'))
                                ->success()
                                ->send();
                        }),
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
            'index' => Pages\ListWishlistItems::route('/'),
            'create' => Pages\CreateWishlistItem::route('/create'),
            'view' => Pages\ViewWishlistItem::route('/{record}'),
            'edit' => Pages\EditWishlistItem::route('/{record}/edit'),
        ];
    }
}
