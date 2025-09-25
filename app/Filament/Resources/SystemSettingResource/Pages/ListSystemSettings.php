<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingResource\Pages;

use App\Filament\Resources\SystemSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

final class ListSystemSettings extends ListRecords
{
    protected static string $resource = SystemSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            Actions\DeleteBulkAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('system_settings.tabs.all')),
            'general' => Tab::make(__('system_settings.tabs.general'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('category', 'general'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('category', 'general')->count()),
            'appearance' => Tab::make(__('system_settings.tabs.appearance'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('category', 'appearance'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('category', 'appearance')->count()),
            'email' => Tab::make(__('system_settings.tabs.email'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('category', 'email'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('category', 'email')->count()),
            'payment' => Tab::make(__('system_settings.tabs.payment'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('category', 'payment'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('category', 'payment')->count()),
            'shipping' => Tab::make(__('system_settings.tabs.shipping'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('category', 'shipping'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('category', 'shipping')->count()),
            'security' => Tab::make(__('system_settings.tabs.security'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('category', 'security'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('category', 'security')->count()),
            'performance' => Tab::make(__('system_settings.tabs.performance'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('category', 'performance'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('category', 'performance')->count()),
            'integration' => Tab::make(__('system_settings.tabs.integration'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('category', 'integration'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('category', 'integration')->count()),
            'analytics' => Tab::make(__('system_settings.tabs.analytics'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('category', 'analytics'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('category', 'analytics')->count()),
            'maintenance' => Tab::make(__('system_settings.tabs.maintenance'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('category', 'maintenance'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('category', 'maintenance')->count()),
            'active' => Tab::make(__('system_settings.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_active', true)->count()),
            'public' => Tab::make(__('system_settings.tabs.public'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_public', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_public', true)->count()),
        ];
    }
}
