<?php

declare(strict_types=1);
declare(strict_types=1);
declare(strict_types=1);
declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\UserWishlistResource\Pages;
use App\Models\UserWishlist;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

/**
 * UserWishlistResource
 *
 * Filament v4 resource for UserWishlist management in the admin panel with comprehensive CRUD operations, filters, and actions.
 */
final class UserWishlistResource extends Resource
{
    protected static ?string $model = UserWishlist::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-heart';

    protected static UnitEnum|string|null $navigationGroup = 'Users';

    protected static ?int $navigationSort = 8;

    public static function getNavigationLabel(): string
    {
        return __('admin.user_wishlists.navigation_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.user_wishlists.plural_model_label');
    }

    public static function getModelLabel(): string
    {
        return __('admin.user_wishlists.model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $form
            ->components([
                Select::make('user_id')
                    ->label(__('admin.user_wishlists.user'))
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                TextInput::make('name')
                    ->label(__('admin.user_wishlists.name'))
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label(__('admin.user_wishlists.description'))
                    ->rows(3)
                    ->columnSpanFull(),
                Toggle::make('is_public')
                    ->label(__('admin.user_wishlists.is_public'))
                    ->default(false),
                Toggle::make('is_default')
                    ->label(__('admin.user_wishlists.is_default'))
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('admin.user_wishlists.user'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('admin.user_wishlists.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label(__('admin.user_wishlists.description'))
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 50 ? $state : null;
                    })
                    ->toggleable(),
                IconColumn::make('is_public')
                    ->label(__('admin.user_wishlists.is_public'))
                    ->boolean(),
                IconColumn::make('is_default')
                    ->label(__('admin.user_wishlists.is_default'))
                    ->boolean(),
                TextColumn::make('wishlist_items_count')
                    ->label(__('admin.user_wishlists.items_count'))
                    ->counts('items')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin.user_wishlists.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin.user_wishlists.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label(__('admin.user_wishlists.user'))
                    ->relationship('user', 'name')
                    ->searchable(),
                TernaryFilter::make('is_public')
                    ->label(__('admin.user_wishlists.is_public')),
                TernaryFilter::make('is_default')
                    ->label(__('admin.user_wishlists.is_default')),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('toggle_public')
                    ->label(fn (UserWishlist $record): string => $record->is_public ? __('admin.user_wishlists.make_private') : __('admin.user_wishlists.make_public'))
                    ->icon(fn (UserWishlist $record): string => $record->is_public ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (UserWishlist $record): string => $record->is_public ? 'warning' : 'success')
                    ->action(function (UserWishlist $record): void {
                        $record->update(['is_public' => ! $record->is_public]);
                        Notification::make()
                            ->title($record->is_public ? __('admin.user_wishlists.made_public_successfully') : __('admin.user_wishlists.made_private_successfully'))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('make_public')
                        ->label(__('admin.user_wishlists.make_public'))
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_public' => true]);
                            Notification::make()
                                ->title(__('admin.user_wishlists.made_public_successfully'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    BulkAction::make('make_private')
                        ->label(__('admin.user_wishlists.make_private'))
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_public' => false]);
                            Notification::make()
                                ->title(__('admin.user_wishlists.made_private_successfully'))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
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
            'index' => Pages\ListUserWishlists::route('/'),
            'create' => Pages\CreateUserWishlist::route('/create'),
            'view' => Pages\ViewUserWishlist::route('/{record}'),
            'edit' => Pages\EditUserWishlist::route('/{record}/edit'),
        ];
    }
}
