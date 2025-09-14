<?php

declare (strict_types=1);
namespace App\Filament\Resources\ReferralResource\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
/**
 * TopReferrersWidget
 * 
 * Filament v4 resource for TopReferrersWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property int|string|array $columnSpan
 * @property string|null $heading
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class TopReferrersWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = null;
    /**
     * Handle getHeading functionality with proper error handling.
     * @return string
     */
    public function getHeading(): string
    {
        return __('referrals.top_referrers');
    }
    /**
     * Configure the Filament table with columns, filters, and actions.
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table->query(User::query()->withCount(['referrals as total_referrals'])->withCount(['referrals as completed_referrals' => function (Builder $query) {
            $query->where('status', 'completed');
        }])->withCount(['referrals as pending_referrals' => function (Builder $query) {
            $query->where('status', 'pending');
        }])->withSum('referralRewards', 'amount')->having('total_referrals', '>', 0)->orderBy('total_referrals', 'desc'))->columns([Tables\Columns\TextColumn::make('name')->label(__('referrals.referrer'))->searchable()->sortable()->weight('bold'), Tables\Columns\TextColumn::make('email')->label(__('referrals.email'))->searchable()->sortable()->toggleable(), Tables\Columns\TextColumn::make('total_referrals')->label(__('referrals.total_referrals'))->sortable()->badge()->color('primary'), Tables\Columns\TextColumn::make('completed_referrals')->label(__('referrals.completed_referrals'))->sortable()->badge()->color('success'), Tables\Columns\TextColumn::make('pending_referrals')->label(__('referrals.pending_referrals'))->sortable()->badge()->color('warning'), Tables\Columns\TextColumn::make('referral_rewards_sum_amount')->label(__('referrals.total_rewards'))->money('EUR')->sortable()->badge()->color('success'), Tables\Columns\TextColumn::make('conversion_rate')->label(__('referrals.conversion_rate'))->getStateUsing(function (User $record): string {
            if ($record->total_referrals == 0) {
                return '0%';
            }
            return round($record->completed_referrals / $record->total_referrals * 100, 1) . '%';
        })->badge()->color(fn(string $state): string => match (true) {
            (float) $state >= 50 => 'success',
            (float) $state >= 25 => 'warning',
            default => 'danger',
        })])->defaultSort('total_referrals', 'desc')->paginated([10, 25, 50])->poll('60s');
    }
}