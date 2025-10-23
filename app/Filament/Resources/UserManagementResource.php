<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\UserManagementResource\Pages;
use App\Filament\Resources\UserManagementResource\RelationManagers\AddressesRelationManager;
use App\Filament\Resources\UserManagementResource\RelationManagers\OrdersRelationManager;
use App\Filament\Resources\UserManagementResource\RelationManagers\ReviewsRelationManager;
use App\Filament\Resources\UserResource;
use App\Models\Scopes\ActiveScope;
use App\Models\User;
use App\Support\Authorization\AuthorizationMatrix;
use BackedEnum;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

final class UserManagementResource extends Resource
{
    protected static ?string $model = User::class;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $slug = 'user-management';

    protected static ?int $navigationSort = 3;

    protected static UnitEnum|string|null $navigationGroup = 'Users';

    public static function shouldRegisterNavigation(): bool
    {
        return AuthorizationMatrix::check('users', 'viewAny');
    }

    public static function canViewAny(): bool
    {
        return AuthorizationMatrix::check('users', 'viewAny');
    }

    public static function canView(Model $record): bool
    {
        return AuthorizationMatrix::check('users', 'view');
    }

    public static function canCreate(): bool
    {
        return AuthorizationMatrix::check('users', 'create');
    }

    public static function canEdit(Model $record): bool
    {
        return AuthorizationMatrix::check('users', 'update');
    }

    public static function canDelete(Model $record): bool
    {
        return AuthorizationMatrix::check('users', 'delete');
    }

    public static function canForceDelete(Model $record): bool
    {
        return AuthorizationMatrix::check('users', 'delete');
    }

    public static function canRestore(Model $record): bool
    {
        return AuthorizationMatrix::check('users', 'update');
    }

    public static function getNavigationLabel(): string
    {
        return __('users.title');
    }

    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return static::$navigationGroup;
    }

    public static function getPluralModelLabel(): string
    {
        return __('users.plural');
    }

    public static function getModelLabel(): string
    {
        return __('users.single');
    }

    public static function form(Form $form): Form
    {
        return UserResource::form($form);
    }

    public static function table(Table $table): Table
    {
        return UserResource::table($table);
    }

    public static function getRelations(): array
    {
        return [
            AddressesRelationManager::class,
            OrdersRelationManager::class,
            ReviewsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            ActiveScope::class,
        ]);
    }

    public static function getGlobalSearchResultAttributes(): array
    {
        return ['name', 'email'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return array_filter([
            __('users.fields.email') => $record->email,
            __('users.fields.locale') => $record->locale,
        ]);
    }

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return static::getUrl('view', ['record' => $record]);
    }
}
