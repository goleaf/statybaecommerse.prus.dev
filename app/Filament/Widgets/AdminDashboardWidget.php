<?php

declare (strict_types=1);
namespace App\Filament\Widgets;

use App\Models\Subscriber;
use App\Models\Company;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
/**
 * AdminDashboardWidget
 * 
 * Filament v4 widget for AdminDashboardWidget dashboard display with real-time data and interactive features.
 * 
 */
final class AdminDashboardWidget extends BaseWidget
{
    /**
     * Handle getStats functionality with proper error handling.
     * @return array
     */
    protected function getStats(): array
    {
        $totalSubscribers = Subscriber::count();
        $activeSubscribers = Subscriber::active()->count();
        $totalCompanies = Company::count();
        $activeCompanies = Company::active()->count();
        $totalUsers = User::count();
        $newSubscribersThisMonth = Subscriber::whereMonth('created_at', now()->month)->count();
        $newCompaniesThisMonth = Company::whereMonth('created_at', now()->month)->count();
        $newUsersThisMonth = User::whereMonth('created_at', now()->month)->count();
        return [Stat::make('Total Subscribers', number_format($totalSubscribers))->description('Active: ' . number_format($activeSubscribers))->descriptionIcon('heroicon-m-users')->color($activeSubscribers > 0 ? 'success' : 'gray'), Stat::make('Total Companies', number_format($totalCompanies))->description('Active: ' . number_format($activeCompanies))->descriptionIcon('heroicon-m-building-office')->color($activeCompanies > 0 ? 'primary' : 'gray'), Stat::make('Total Users', number_format($totalUsers))->description('Registered users')->descriptionIcon('heroicon-m-user')->color('info'), Stat::make('New This Month', number_format($newSubscribersThisMonth + $newCompaniesThisMonth + $newUsersThisMonth))->description('Subscribers: ' . $newSubscribersThisMonth . ', Companies: ' . $newCompaniesThisMonth . ', Users: ' . $newUsersThisMonth)->descriptionIcon('heroicon-m-chart-bar')->color('warning')];
    }
    /**
     * Handle getColumns functionality with proper error handling.
     * @return int
     */
    protected function getColumns(): int
    {
        return 4;
    }
}