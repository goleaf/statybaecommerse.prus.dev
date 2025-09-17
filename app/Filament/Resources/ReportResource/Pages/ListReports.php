<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReportResource\Pages;

use App\Filament\Resources\ReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

final class ListReports extends ListRecords
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('reports.tabs.all')),
            
            'sales' => Tab::make(__('reports.tabs.sales'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'sales'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'sales')->count()),
            
            'inventory' => Tab::make(__('reports.tabs.inventory'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'inventory'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'inventory')->count()),
            
            'customer' => Tab::make(__('reports.tabs.customer'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'customer'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'customer')->count()),
            
            'product' => Tab::make(__('reports.tabs.product'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'product'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'product')->count()),
            
            'financial' => Tab::make(__('reports.tabs.financial'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'financial'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'financial')->count()),
            
            'analytics' => Tab::make(__('reports.tabs.analytics'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'analytics'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'analytics')->count()),
            
            'active' => Tab::make(__('reports.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_active', true)->count()),
            
            'scheduled' => Tab::make(__('reports.tabs.scheduled'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_scheduled', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_scheduled', true)->count()),
        ];
    }
}

