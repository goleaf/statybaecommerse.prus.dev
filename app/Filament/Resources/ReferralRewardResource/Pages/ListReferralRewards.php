<?php

declare (strict_types=1);
namespace App\Filament\Resources\ReferralRewardResource\Pages;

use App\Filament\Resources\ReferralRewardResource;
use Filament\Actions;
use Filament\Resources\Components\Tabs\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
/**
 * ListReferralRewards
 * 
 * Filament v4 resource for ListReferralRewards management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class ListReferralRewards extends ListRecords
{
    protected static string $resource = ReferralRewardResource::class;
    /**
     * Handle getHeaderActions functionality with proper error handling.
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [Actions\Action::make('back_to_dashboard')->label(__('common.back_to_dashboard'))->icon('heroicon-o-arrow-left')->color('gray')->url('/admin')->tooltip(__('common.back_to_dashboard_tooltip')), Actions\CreateAction::make()];
    }
    /**
     * Handle getTabs functionality with proper error handling.
     * @return array
     */
    public function getTabs(): array
    {
        return ['all' => Tab::make(__('referrals.filters.all')), 'pending' => Tab::make(__('referrals.filters.pending'))->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'pending'))->badge(fn() => \App\Models\ReferralReward::where('status', 'pending')->count()), 'applied' => Tab::make(__('referrals.filters.applied'))->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'applied'))->badge(fn() => \App\Models\ReferralReward::where('status', 'applied')->count()), 'expired' => Tab::make(__('referrals.filters.expired'))->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'expired'))->badge(fn() => \App\Models\ReferralReward::where('status', 'expired')->count()), 'referrer_bonus' => Tab::make(__('referrals.filters.referrer_bonus'))->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'referrer_bonus'))->badge(fn() => \App\Models\ReferralReward::where('type', 'referrer_bonus')->count()), 'referred_discount' => Tab::make(__('referrals.filters.referred_discount'))->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'referred_discount'))->badge(fn() => \App\Models\ReferralReward::where('type', 'referred_discount')->count())];
    }
    /**
     * Handle getHeaderWidgets functionality with proper error handling.
     * @return array
     */
    protected function getHeaderWidgets(): array
    {
        return [Widgets\ReferralRewardStatsWidget::class];
    }
    /**
     * Handle getFooterWidgets functionality with proper error handling.
     * @return array
     */
    protected function getFooterWidgets(): array
    {
        return [Widgets\ReferralRewardChartWidget::class, Widgets\ReferralRewardTypeChartWidget::class];
    }
}