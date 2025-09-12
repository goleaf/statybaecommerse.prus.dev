<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\CustomerGroup;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

final class CustomerGroupStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalGroups = CustomerGroup::count();
        $activeGroups = CustomerGroup::where('is_enabled', true)->count();
        $groupsWithDiscount = CustomerGroup::where('discount_percentage', '>', 0)->count();
        $totalCustomers = DB::table('customer_group_user')->count();
        $averageDiscount = CustomerGroup::where('discount_percentage', '>', 0)->avg('discount_percentage') ?? 0;

        return [
            Stat::make(__('customer_groups.widget_total_groups'), $totalGroups)
                ->description(__('customer_groups.widget_total_groups'))
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),
                
            Stat::make(__('customer_groups.widget_active_groups'), $activeGroups)
                ->description(__('customer_groups.widget_active_groups'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
                
            Stat::make(__('customer_groups.widget_groups_with_discount'), $groupsWithDiscount)
                ->description(__('customer_groups.widget_groups_with_discount'))
                ->descriptionIcon('heroicon-m-tag')
                ->color('warning'),
                
            Stat::make(__('customer_groups.widget_total_customers'), $totalCustomers)
                ->description(__('customer_groups.widget_total_customers'))
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
                
            Stat::make(__('customer_groups.widget_average_discount'), number_format($averageDiscount, 2) . '%')
                ->description(__('customer_groups.widget_average_discount'))
                ->descriptionIcon('heroicon-m-percent')
                ->color('danger'),
        ];
    }
}
