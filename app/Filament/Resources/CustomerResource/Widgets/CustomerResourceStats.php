<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerResource\Widgets;

use App\Filament\Support\InteractsWithDateFilter;
use App\Models\Customer;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget as BaseWidget;
use EightyNine\FilamentAdvancedWidget\AdvancedStatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Number;

final class CustomerResourceStats extends BaseWidget
{
    use InteractsWithDateFilter;

    protected static ?string $heading = 'Customer health';

    protected static ?string $badge = 'Customers';

    protected static ?string $badgeColor = 'success';

    public ?string $filter = 'month';

    protected function getFilters(): ?array
    {
        return [
            'today'   => __('Today'),
            'week'    => __('Last week'),
            'month'   => __('Last month'),
            'quarter' => __('This quarter'),
            'year'    => __('This year'),
        ];
    }

    protected function getStats(): array
    {
        [$from, $to] = $this->getDateRange($this->filter);

        /** @var array{
         *     total: int,
         *     active: int,
         *     inactive: int,
         *     new: int,
         *     engaged: int
         * } $metrics
         */
        $metrics = Cache::remember(
            sprintf('filament:widgets:customer-stats:%s:%s:%s', $this->filter, $from->format('Ymd'), $to->format('Ymd')),
            now()->addMinutes(5),
            static function () use ($from, $to): array {
                $engaged = 0;

                if (Schema::hasColumn('orders', 'customer_id')) {
                    $engaged = Customer::query()
                        ->whereHas('orders', static function ($query) use ($from, $to): void {
                            $query->whereBetween('created_at', [$from, $to]);
                        })
                        ->count();
                }

                return [
                    'total'    => (int) Customer::count(),
                    'active'   => (int) Customer::where('is_active', true)->count(),
                    'inactive' => (int) Customer::where('is_active', false)->count(),
                    'new'      => (int) Customer::whereBetween('created_at', [$from, $to])->count(),
                    'engaged'  => $engaged,
                ];
            }
        );

        return [
            Stat::make(__('Total customers'), Number::format($metrics['total']))
                ->icon('heroicon-o-users')
                ->iconBackgroundColor('primary')
                ->description(__('All customers in the CRM')),
            Stat::make(__('Active customers'), Number::format($metrics['active']))
                ->icon('heroicon-o-check-circle')
                ->iconBackgroundColor('success')
                ->description(__('Marked as active')),
            Stat::make(__('New customers'), Number::format($metrics['new']))
                ->icon('heroicon-o-user-plus')
                ->iconBackgroundColor('info')
                ->description(__('Created during the selected period')),
            Stat::make(__('Engaged customers'), Number::format($metrics['engaged']))
                ->icon('heroicon-o-heart')
                ->iconBackgroundColor('warning')
                ->description(__('Placed an order in the selected period')),
            Stat::make(__('Inactive customers'), Number::format($metrics['inactive']))
                ->icon('heroicon-o-user-minus')
                ->iconBackgroundColor('secondary')
                ->description(__('Currently inactive')),
        ];
    }
}
