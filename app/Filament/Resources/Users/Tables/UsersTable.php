<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use App\Enums\ExportType;
use App\Filament\Actions\RequestExportBulkAction;
use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('messages.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('account_type')
                    ->label(__('messages.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'private' => 'gray',
                        'company' => 'success',
                        default   => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('messages.email'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone_number')
                    ->label(__('messages.phone'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('first_name')
                    ->sortable()
                    ->label(__('messages.first_name'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('last_name')
                    ->sortable()
                    ->label(__('messages.last_name'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('job_title')
                    ->sortable()
                    ->label(__('messages.job_title'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('company')
                    ->sortable()
                    ->label(__('messages.company'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('companyRelation.name')
                    ->label(__('messages.company'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label(__('messages.active'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('preferred_locale')
                    ->label(__('messages.locale'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('orders_count')
                    ->label(__('messages.orders'))
                    ->counts('orders')
                    ->sortable(),
                TextColumn::make('customerGroups.name')
                    ->label(__('admin.navigation.customer_groups'))
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('addresses_count')
                    ->label(__('messages.address'))
                    ->counts('addresses')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cart_items_count')
                    ->label(__('messages.cart_items'))
                    ->counts('cartItems')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('customer_groups_count')
                    ->label(__('admin.navigation.customer_groups'))
                    ->counts('customerGroups')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('partners_count')
                    ->label(__('messages.partners'))
                    ->counts('partners')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('referral_codes_count')
                    ->label(__('messages.referral_codes'))
                    ->counts('referralCodes')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('referrals_count')
                    ->label(__('messages.referrals'))
                    ->counts('referrals')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('referral_rewards_count')
                    ->label(__('admin.labels.referral_rewards'))
                    ->counts('referralRewards')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('coupon_usages_count')
                    ->label(__('admin.labels.coupon_usages'))
                    ->counts('couponUsages')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('discount_redemptions_count')
                    ->label(__('messages.discount_redemptions'))
                    ->counts('discountRedemptions')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('notifications_count')
                    ->label(__('messages.notifications'))
                    ->counts('notifications')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('subscriber_count')
                    ->label(__('navigation.subscribers'))
                    ->counts('subscriber')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('messages.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('messages.active')),
            ])
            ->recordUrl(fn (User $record): string => UserResource::getUrl('edit', ['record' => $record]))
            ->recordActions([
                Action::make('view')
                    ->icon('heroicon-m-eye')
                    ->url(fn (User $record): string => UserResource::getUrl('view', ['record' => $record])),
                Action::make('edit')
                    ->icon('heroicon-m-pencil-square')
                    ->url(fn (User $record): string => UserResource::getUrl('edit', ['record' => $record])),
            ])
            ->bulkActions([
                RequestExportBulkAction::make(ExportType::USERS),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
