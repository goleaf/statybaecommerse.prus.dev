<?php declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CartItemResource\Pages;
use App\Models\CartItem;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
use UnitEnum;

final class CartItemResource extends Resource
{
    protected static ?string $model = CartItem::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static string|UnitEnum|null $navigationGroup = \App\Enums\NavigationGroup::Orders;

    protected static ?string $navigationLabel = null;

    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.navigation.orders');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.cart_items');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make(__('admin.cart.items'))
                    ->components([
                        Forms\Components\TextInput::make('session_id')
                            ->label(__('admin.cart.fields.session_id'))
                            ->maxLength(255),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('product_id')
                            ->relationship('product', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('variant_id')
                            ->relationship('variant', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('quantity')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(1),
                        Forms\Components\TextInput::make('unit_price')
                            ->required()
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01),
                        Forms\Components\TextInput::make('total_price')
                            ->required()
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01),
                    ])
                    ->columns(2),
                \Filament\Schemas\Components\Section::make(__('admin.products.view'))
                    ->components([
                        Forms\Components\KeyValue::make('product_snapshot')
                            ->label(__('admin.products.fields.description'))
                            ->keyLabel('Key')
                            ->valueLabel('Value'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('admin.cart.fields.user'))
                    ->sortable()
                    ->searchable()
                    ->placeholder('Guest'),
                Tables\Columns\TextColumn::make('session_id')
                    ->label(__('admin.cart.fields.session_id'))
                    ->limit(10)
                    ->tooltip(fn($record) => $record->session_id),
                Tables\Columns\TextColumn::make('product.name')
                    ->label(__('admin.cart.fields.product'))
                    ->sortable()
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('variant.name')
                    ->label(__('admin.cart.fields.variant'))
                    ->placeholder('No variant')
                    ->limit(20),
                Tables\Columns\TextColumn::make('quantity')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('unit_price')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->money('EUR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('admin.cart.fields.added_at'))
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_user')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('user_id'))
                    ->label('Registered Users Only'),
                Tables\Filters\Filter::make('guest_only')
                    ->query(fn(Builder $query): Builder => $query->whereNull('user_id'))
                    ->label('Guest Users Only'),
                Tables\Filters\Filter::make('created_today')
                    ->query(fn(Builder $query): Builder => $query->whereDate('created_at', today()))
                    ->label('Added Today'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('clear_old_carts')
                        ->label(__('admin.cart.clear_old'))
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->action(function ($records) {
                            // Respect selected records if provided; otherwise, run global cleanup
                            if ($records && count($records)) {
                                foreach ($records as $record) {
                                    if ($record->created_at < now()->subDays(7)) {
                                        $record->forceDelete();
                                    }
                                }
                            } else {
                                CartItem::where('created_at', '<', now()->subDays(7))->forceDelete();
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading(__('admin.cart.clear_old'))
                        ->modalDescription(__('admin.messages.confirm_delete'))
                        ->modalSubmitActionLabel(__('admin.actions.confirm')),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCartItems::route('/'),
            'create' => Pages\CreateCartItem::route('/create'),
            'view' => Pages\ViewCartItem::route('/{record}'),
            'edit' => Pages\EditCartItem::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['user.name', 'product.name', 'session_id'];
    }
}
