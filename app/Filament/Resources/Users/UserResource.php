<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users;

use App\Models\AdminUser;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Schemas\UserInfolist;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    public static function getModelLabel(): string
    {
        return __('admin.users.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.users.plural_model_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.navigation.users');
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrdersRelationManager::class,
            RelationManagers\AddressesRelationManager::class,
            RelationManagers\CustomerGroupsRelationManager::class,
            RelationManagers\PartnersRelationManager::class,
            RelationManagers\ReferralCodesRelationManager::class,
            RelationManagers\DocumentsRelationManager::class,
            RelationManagers\ReferralsRelationManager::class,
            RelationManagers\CouponUsagesRelationManager::class,
            RelationManagers\DiscountRedemptionsRelationManager::class,
            RelationManagers\ReferralRewardsRelationManager::class,
            RelationManagers\NotificationsRelationManager::class,
            RelationManagers\SubscriberRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view'   => ViewUser::route('/{record}'),
            'edit'   => EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->where(function (Builder $query): void {
                $query->whereNull('is_admin')
                    ->orWhere('is_admin', false);
            })
            ->whereNotIn('email', AdminUser::query()->select('email'));
    }
}
