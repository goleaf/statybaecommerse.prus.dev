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
            'all'     => Tab::make(__('common.all')),
            'company' => Tab::make(__('messages.company'))
                ->modifyQueryUsing(static fn (Builder $query): Builder => $query->whereNotNull('company_id')),
            'addresses'            => self::relationTab('addresses', __('messages.address')),
            'customer_groups'      => self::relationTab('customerGroups', __('admin.navigation.customer_groups')),
            'partners'             => self::relationTab('partners', __('messages.partners')),
            'referral_codes'       => self::relationTab('referralCodes', __('messages.referral_codes')),
            'referrals'            => self::relationTab('referrals', __('messages.referrals')),
            'referral_rewards'     => self::relationTab('referralRewards', __('messages.referral_rewards')),
            'coupon_usages'        => self::relationTab('couponUsages', __('messages.coupon_usages')),
            'discount_redemptions' => self::relationTab('discountRedemptions', __('messages.discount_redemptions')),
            'notifications'        => self::relationTab('notifications', __('messages.notifications')),
            'subscriber'           => self::relationTab('subscriber', __('navigation.subscribers')),
        ];
    }

    private static function relationTab(string $relation, string $label): Tab
    {
        return Tab::make($label)
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
