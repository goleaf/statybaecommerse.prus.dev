<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemSettingResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\SystemSettingResource;
use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;

class ListSystemSettings extends BaseListRecords
{
    use HasWidgetTabs;

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
            'all' => WidgetTab::make(__('system_settings.tabs.all'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->count()),
            'general' => WidgetTab::make(__('system_settings.tabs.general'))
                ->modifyQueryUsing(fn (Builder $query) => $this->scopeCategorySlug($query, 'general'))
                ->value(fn () => $this->scopeCategorySlug($this->getResource()::getEloquentQuery(), 'general')->count()),
            'appearance' => WidgetTab::make(__('system_settings.tabs.appearance'))
                ->modifyQueryUsing(fn (Builder $query) => $this->scopeCategorySlug($query, 'appearance'))
                ->value(fn () => $this->scopeCategorySlug($this->getResource()::getEloquentQuery(), 'appearance')->count()),
            'email' => WidgetTab::make(__('system_settings.tabs.email'))
                ->modifyQueryUsing(fn (Builder $query) => $this->scopeCategorySlug($query, 'email'))
                ->value(fn () => $this->scopeCategorySlug($this->getResource()::getEloquentQuery(), 'email')->count()),
            'payment' => WidgetTab::make(__('system_settings.tabs.payment'))
                ->modifyQueryUsing(fn (Builder $query) => $this->scopeCategorySlug($query, 'payment'))
                ->value(fn () => $this->scopeCategorySlug($this->getResource()::getEloquentQuery(), 'payment')->count()),
            'shipping' => WidgetTab::make(__('system_settings.tabs.shipping'))
                ->modifyQueryUsing(fn (Builder $query) => $this->scopeCategorySlug($query, 'shipping'))
                ->value(fn () => $this->scopeCategorySlug($this->getResource()::getEloquentQuery(), 'shipping')->count()),
            'security' => WidgetTab::make(__('system_settings.tabs.security'))
                ->modifyQueryUsing(fn (Builder $query) => $this->scopeCategorySlug($query, 'security'))
                ->value(fn () => $this->scopeCategorySlug($this->getResource()::getEloquentQuery(), 'security')->count()),
            'performance' => WidgetTab::make(__('system_settings.tabs.performance'))
                ->modifyQueryUsing(fn (Builder $query) => $this->scopeCategorySlug($query, 'performance'))
                ->value(fn () => $this->scopeCategorySlug($this->getResource()::getEloquentQuery(), 'performance')->count()),
            'integration' => WidgetTab::make(__('system_settings.tabs.integration'))
                ->modifyQueryUsing(fn (Builder $query) => $this->scopeCategorySlug($query, 'integration'))
                ->value(fn () => $this->scopeCategorySlug($this->getResource()::getEloquentQuery(), 'integration')->count()),
            'analytics' => WidgetTab::make(__('system_settings.tabs.analytics'))
                ->modifyQueryUsing(fn (Builder $query) => $this->scopeCategorySlug($query, 'analytics'))
                ->value(fn () => $this->scopeCategorySlug($this->getResource()::getEloquentQuery(), 'analytics')->count()),
            'maintenance' => WidgetTab::make(__('system_settings.tabs.maintenance'))
                ->modifyQueryUsing(fn (Builder $query) => $this->scopeCategorySlug($query, 'maintenance'))
                ->value(fn () => $this->scopeCategorySlug($this->getResource()::getEloquentQuery(), 'maintenance')->count()),
            'active' => WidgetTab::make(__('system_settings.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('is_active', true)->count()),
            'public' => WidgetTab::make(__('system_settings.tabs.public'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_public', true))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('is_public', true)->count()),
        ];
    }

    private function scopeCategorySlug(Builder $query, string $slug): Builder
    {
        // Route widget queries through the relation helper so counts remain accurate
        // even while the legacy `category` column still exists on the table.
        return $query->whereHas('categoryRelation', fn (Builder $relation) => $relation->where('slug', $slug));
    }
}
