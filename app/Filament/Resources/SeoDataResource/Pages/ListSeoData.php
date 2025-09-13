<?php

declare(strict_types=1);

namespace App\Filament\Resources\SeoDataResource\Pages;

use App\Filament\Resources\SeoDataResource;
use App\Filament\Resources\SeoDataResource\Widgets;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

final class ListSeoData extends ListRecords
{
    protected static string $resource = SeoDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back_to_dashboard')
                ->label(__('common.back_to_dashboard'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url('/admin')
                ->tooltip(__('common.back_to_dashboard_tooltip')),
            Actions\CreateAction::make()
                ->label(__('admin.seo_data.create')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            Widgets\SeoDataOverviewWidget::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('admin.common.all'))
                ->icon('heroicon-o-list-bullet'),

            'products' => Tab::make(__('admin.models.products'))
                ->icon('heroicon-o-cube')
                ->modifyQueryUsing(fn (Builder $query) => $query->forProducts()),

            'categories' => Tab::make(__('admin.models.categories'))
                ->icon('heroicon-o-squares-2x2')
                ->modifyQueryUsing(fn (Builder $query) => $query->forCategories()),

            'brands' => Tab::make(__('admin.models.brands'))
                ->icon('heroicon-o-tag')
                ->modifyQueryUsing(fn (Builder $query) => $query->forBrands()),

            'lithuanian' => Tab::make('Lietuvių')
                ->icon('heroicon-o-language')
                ->modifyQueryUsing(fn (Builder $query) => $query->forLocale('lt')),

            'english' => Tab::make('English')
                ->icon('heroicon-o-language')
                ->modifyQueryUsing(fn (Builder $query) => $query->forLocale('en')),

            'needs_optimization' => Tab::make(__('admin.seo_data.seo_analysis.needs_optimization'))
                ->icon('heroicon-o-exclamation-triangle')
                ->modifyQueryUsing(function (Builder $query) {
                    return $query->where(function (Builder $query) {
                        $query->whereNull('title')
                            ->orWhereNull('description')
                            ->orWhereNull('keywords')
                            ->orWhereNull('canonical_url')
                            ->orWhereNull('structured_data');
                    });
                }),

            'excellent_seo' => Tab::make(__('admin.seo_data.seo_score.excellent'))
                ->icon('heroicon-o-star')
                ->modifyQueryUsing(function (Builder $query) {
                    return $query->whereNotNull('title')
                        ->whereNotNull('description')
                        ->whereNotNull('keywords')
                        ->whereNotNull('canonical_url')
                        ->whereNotNull('structured_data')
                        ->where('no_index', false)
                        ->where('no_follow', false);
                }),
        ];
    }
}
