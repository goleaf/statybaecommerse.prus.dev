<?php

declare (strict_types=1);
namespace App\Filament\Resources\CompanyResource\Widgets;

use App\Models\Company;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
/**
 * CompanyStatsWidget
 * 
 * Filament v4 resource for CompanyStatsWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class CompanyStatsWidget extends BaseWidget
{
    /**
     * Handle getStats functionality with proper error handling.
     * @return array
     */
    protected function getStats(): array
    {
        $totalCompanies = Company::count();
        $activeCompanies = Company::active()->count();
        $constructionCompanies = Company::byIndustry('construction')->count();
        $totalSubscribers = Company::withCount('subscribers')->get()->sum('subscribers_count');
        return [Stat::make('Total Companies', number_format($totalCompanies))->description('All companies in system')->descriptionIcon('heroicon-m-building-office')->color('primary'), Stat::make('Active Companies', number_format($activeCompanies))->description(sprintf('%.1f%% of total', $totalCompanies > 0 ? $activeCompanies / $totalCompanies * 100 : 0))->descriptionIcon('heroicon-m-check-circle')->color('success'), Stat::make('Construction Companies', number_format($constructionCompanies))->description('Construction industry')->descriptionIcon('heroicon-m-wrench-screwdriver')->color('info'), Stat::make('Total Subscribers', number_format($totalSubscribers))->description('Across all companies')->descriptionIcon('heroicon-m-users')->color('warning')];
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