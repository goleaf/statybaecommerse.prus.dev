<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\RelationManagers\AddressesRelationManager;
use App\Filament\Resources\Users\RelationManagers\CartItemsRelationManager;
use App\Filament\Resources\Users\RelationManagers\CouponUsagesRelationManager;
use App\Filament\Resources\Users\RelationManagers\CustomerGroupsRelationManager;
use App\Filament\Resources\Users\RelationManagers\DiscountRedemptionsRelationManager;
use App\Filament\Resources\Users\RelationManagers\DocumentsRelationManager;
use App\Filament\Resources\Users\RelationManagers\NotificationsRelationManager;
use App\Filament\Resources\Users\RelationManagers\OrdersRelationManager;
use App\Filament\Resources\Users\RelationManagers\OrganizationsRelationManager;
use App\Filament\Resources\Users\RelationManagers\PartnersRelationManager;
use App\Filament\Resources\Users\RelationManagers\ReferralCodesRelationManager;
use App\Filament\Resources\Users\RelationManagers\ReferralRewardsRelationManager;
use App\Filament\Resources\Users\RelationManagers\ReferralsRelationManager;
use App\Filament\Resources\Users\RelationManagers\SubscriberRelationManager;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Schemas\UserInfolist;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\AdminUser;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 0;

    protected static bool $shouldRegisterNavigation = true;

    public static function getModelLabel(): string
    {
        return 'Customer';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Customers';
    }

    public static function getNavigationLabel(): string
    {
        return 'Customers';
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
            OrdersRelationManager::class,
            CartItemsRelationManager::class,
            AddressesRelationManager::class,
            CustomerGroupsRelationManager::class,
            OrganizationsRelationManager::class,
            PartnersRelationManager::class,
            ReferralCodesRelationManager::class,
            DocumentsRelationManager::class,
            ReferralsRelationManager::class,
            CouponUsagesRelationManager::class,
            DiscountRedemptionsRelationManager::class,
            ReferralRewardsRelationManager::class,
            NotificationsRelationManager::class,
            SubscriberRelationManager::class,
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
            ])
            ->where(function (Builder $query): void {
                $query->whereNull('is_admin')
                    ->orWhere('is_admin', false);
            })
            ->whereNotIn('email', AdminUser::query()->select('email'));
    }
}
