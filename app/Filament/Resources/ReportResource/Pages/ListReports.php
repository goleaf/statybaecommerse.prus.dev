<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\ReportResource;
use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;
use Illuminate\Database\Eloquent\Builder;

final class ListReports extends BaseListRecords
{
    use HasResizableColumns;
    use HasWidgetTabs;

    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getWidgetTabs(): array
    {
        return [
            'all' => WidgetTab::make(__('reports.tabs.all'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->count()),

            'sales' => WidgetTab::make(__('reports.tabs.sales'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'sales'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('type', 'sales')->count()),

            'inventory' => WidgetTab::make(__('reports.tabs.inventory'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'inventory'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('type', 'inventory')->count()),

            'customer' => WidgetTab::make(__('reports.tabs.customer'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'customer'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('type', 'customer')->count()),

            'product' => WidgetTab::make(__('reports.tabs.product'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'product'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('type', 'product')->count()),

            'financial' => WidgetTab::make(__('reports.tabs.financial'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'financial'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('type', 'financial')->count()),

            'analytics' => WidgetTab::make(__('reports.tabs.analytics'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'analytics'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('type', 'analytics')->count()),

            'active' => WidgetTab::make(__('reports.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('is_active', true)->count()),

            'scheduled' => WidgetTab::make(__('reports.tabs.scheduled'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_scheduled', true))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('is_scheduled', true)->count()),
        ];
    }
}
