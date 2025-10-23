<?php

declare(strict_types=1);

namespace App\Filament\Resources\AttributeValueResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\AttributeValueResource;
use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

final class ListAttributeValues extends BaseListRecords
{
    use HasWidgetTabs;

    protected static string $resource = AttributeValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getWidgetTabs(): array
    {
        return [
            'all' => WidgetTab::make(__('attribute_values.tabs.all'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->count()),

            'active' => WidgetTab::make(__('attribute_values.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('is_active', true)->count()),

            'color' => WidgetTab::make(__('attribute_values.tabs.color'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('attribute', function ($q) {
                    $q->where('type', 'color');
                }))
                ->value(fn () => $this->getResource()::getEloquentQuery()->whereHas('attribute', function ($q) {
                    $q->where('type', 'color');
                })->count()),

            'size' => WidgetTab::make(__('attribute_values.tabs.size'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('attribute', function ($q) {
                    $q->where('type', 'size');
                }))
                ->value(fn () => $this->getResource()::getEloquentQuery()->whereHas('attribute', function ($q) {
                    $q->where('type', 'size');
                })->count()),

            'material' => WidgetTab::make(__('attribute_values.tabs.material'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('attribute', function ($q) {
                    $q->where('type', 'material');
                }))
                ->value(fn () => $this->getResource()::getEloquentQuery()->whereHas('attribute', function ($q) {
                    $q->where('type', 'material');
                })->count()),

            'brand' => WidgetTab::make(__('attribute_values.tabs.brand'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('attribute', function ($q) {
                    $q->where('type', 'brand');
                }))
                ->value(fn () => $this->getResource()::getEloquentQuery()->whereHas('attribute', function ($q) {
                    $q->where('type', 'brand');
                })->count()),

            'recent' => WidgetTab::make(__('attribute_values.tabs.recent'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('created_at', '>=', now()->subDays(7)))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('created_at', '>=', now()->subDays(7))->count()),
        ];
    }
}
