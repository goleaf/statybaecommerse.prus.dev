<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralRewardResource\Pages;

use App\Filament\Resources\ReferralRewardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

final class ListReferralRewards extends ListRecords
{
    protected static string $resource = ReferralRewardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('referral_rewards.tabs.all')),
            
            'active' => Tab::make(__('referral_rewards.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_active', true)->count()),
            
            'pending' => Tab::make(__('referral_rewards.tabs.pending'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('status', 'pending')->count()),
            
            'approved' => Tab::make(__('referral_rewards.tabs.approved'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'approved'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('status', 'approved')->count()),
            
            'paid' => Tab::make(__('referral_rewards.tabs.paid'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'paid'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('status', 'paid')->count()),
            
            'cancelled' => Tab::make(__('referral_rewards.tabs.cancelled'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'cancelled'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('status', 'cancelled')->count()),
            
            'discount' => Tab::make(__('referral_rewards.tabs.discount'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'discount'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'discount')->count()),
            
            'credit' => Tab::make(__('referral_rewards.tabs.credit'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'credit'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'credit')->count()),
            
            'cash' => Tab::make(__('referral_rewards.tabs.cash'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'cash'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'cash')->count()),
        ];
    }
}
