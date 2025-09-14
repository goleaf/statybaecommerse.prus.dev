<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Legal;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class LegalStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalDocuments = Legal::count();
        $publishedDocuments = Legal::where('is_enabled', true)->whereNotNull('published_at')->count();
        $draftDocuments = Legal::whereNull('published_at')->count();
        $requiredDocuments = Legal::where('is_required', true)->count();
        $disabledDocuments = Legal::where('is_enabled', false)->count();

        $completionRate = $totalDocuments > 0 ? round(($publishedDocuments / $totalDocuments) * 100, 1) : 0;

        return [
            Stat::make(__('admin.legal.total_documents'), $totalDocuments)
                ->description(__('admin.legal.total_documents_description'))
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make(__('admin.legal.published_documents'), $publishedDocuments)
                ->description(__('admin.legal.published_documents_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('admin.legal.draft_documents'), $draftDocuments)
                ->description(__('admin.legal.draft_documents_description'))
                ->descriptionIcon('heroicon-m-document')
                ->color('warning'),

            Stat::make(__('admin.legal.completion_rate'), $completionRate . '%')
                ->description(__('admin.legal.completion_rate_description'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($completionRate >= 80 ? 'success' : ($completionRate >= 60 ? 'warning' : 'danger')),

            Stat::make(__('admin.legal.required_documents'), $requiredDocuments)
                ->description(__('admin.legal.required_documents_description'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('info'),

            Stat::make(__('admin.legal.disabled_documents'), $disabledDocuments)
                ->description(__('admin.legal.disabled_documents_description'))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}
