<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return [
            'all'     => Tab::make(__('common.all'))
                ->key('all'),
            'company' => Tab::make(__('messages.company'))
                ->key('company')
                ->modifyQueryUsing(static fn (Builder $query): Builder => $query->whereNotNull('company_id')),
            'addresses'            => self::relationTab('addresses', 'addresses', __('messages.address')),
            'cart_items'           => self::relationTab('cart_items', 'cartItems', __('messages.cart_items')),
            'customer_groups'      => self::relationTab('customer_groups', 'customerGroups', __('admin.navigation.customer_groups')),
            'partners'             => self::relationTab('partners', 'partners', __('messages.partners')),
            'referral_codes'       => self::relationTab('referral_codes', 'referralCodes', __('messages.referral_codes')),
            'referrals'            => self::relationTab('referrals', 'referrals', __('messages.referrals')),
            'referral_rewards'     => self::relationTab('referral_rewards', 'referralRewards', __('messages.referral_rewards')),
            'coupon_usages'        => self::relationTab('coupon_usages', 'couponUsages', __('messages.coupon_usages')),
            'discount_redemptions' => self::relationTab('discount_redemptions', 'discountRedemptions', __('messages.discount_redemptions')),
            'notifications'        => self::relationTab('notifications', 'notifications', __('messages.notifications')),
            'subscriber'           => self::relationTab('subscriber', 'subscriber', __('navigation.subscribers')),
        ];
    }

    private static function relationTab(string $key, string $relation, string $label): Tab
    {
        return Tab::make($label)
            ->key($key)
            ->modifyQueryUsing(static fn (Builder $query): Builder => $query->whereHas(
                $relation,
                static fn (Builder $relatedQuery): Builder => $relatedQuery->withoutGlobalScopes(),
            ));
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
