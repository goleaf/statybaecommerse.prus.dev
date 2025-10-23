<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\SystemSettingResource;
use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use Illuminate\Database\Eloquent\Builder;

class ListSystemSettings extends ListRecords
{
    use HasResizableColumns;

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

    public function getWidgetTabs(): array
    {
        return [
            'all'     => WidgetTab::make(__('system_settings.tabs.all'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->count()),
            'general' => WidgetTab::make(__('system_settings.tabs.general'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('category', 'general'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('category', 'general')->count()),
            'appearance' => WidgetTab::make(__('system_settings.tabs.appearance'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('category', 'appearance'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('category', 'appearance')->count()),
            'email' => WidgetTab::make(__('system_settings.tabs.email'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('category', 'email'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('category', 'email')->count()),
            'payment' => WidgetTab::make(__('system_settings.tabs.payment'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('category', 'payment'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('category', 'payment')->count()),
            'shipping' => WidgetTab::make(__('system_settings.tabs.shipping'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('category', 'shipping'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('category', 'shipping')->count()),
            'security' => WidgetTab::make(__('system_settings.tabs.security'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('category', 'security'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('category', 'security')->count()),
            'performance' => WidgetTab::make(__('system_settings.tabs.performance'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('category', 'performance'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('category', 'performance')->count()),
            'integration' => WidgetTab::make(__('system_settings.tabs.integration'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('category', 'integration'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('category', 'integration')->count()),
            'analytics' => WidgetTab::make(__('system_settings.tabs.analytics'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('category', 'analytics'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('category', 'analytics')->count()),
            'maintenance' => WidgetTab::make(__('system_settings.tabs.maintenance'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('category', 'maintenance'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('category', 'maintenance')->count()),
            'active' => WidgetTab::make(__('system_settings.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('is_active', true)->count()),
            'public' => WidgetTab::make(__('system_settings.tabs.public'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_public', true))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('is_public', true)->count()),
        ];
    }
}
