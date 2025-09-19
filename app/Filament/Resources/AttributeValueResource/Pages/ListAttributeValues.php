<?php

declare(strict_types=1);

namespace App\Filament\Resources\AttributeValueResource\Pages;

use App\Filament\Resources\AttributeValueResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

final class ListAttributeValues extends ListRecords
{
    protected static string $resource = AttributeValueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('attribute_values.tabs.all')),
            
            'active' => Tab::make(__('attribute_values.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_active', true)->count()),
            
            'color' => Tab::make(__('attribute_values.tabs.color'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('attribute', function ($q) {
                    $q->where('type', 'color');
                }))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->whereHas('attribute', function ($q) {
                    $q->where('type', 'color');
                })->count()),
            
            'size' => Tab::make(__('attribute_values.tabs.size'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('attribute', function ($q) {
                    $q->where('type', 'size');
                }))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->whereHas('attribute', function ($q) {
                    $q->where('type', 'size');
                })->count()),
            
            'material' => Tab::make(__('attribute_values.tabs.material'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('attribute', function ($q) {
                    $q->where('type', 'material');
                }))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->whereHas('attribute', function ($q) {
                    $q->where('type', 'material');
                })->count()),
            
            'brand' => Tab::make(__('attribute_values.tabs.brand'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('attribute', function ($q) {
                    $q->where('type', 'brand');
                }))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->whereHas('attribute', function ($q) {
                    $q->where('type', 'brand');
                })->count()),
            
            'recent' => Tab::make(__('attribute_values.tabs.recent'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('created_at', '>=', now()->subDays(7)))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('created_at', '>=', now()->subDays(7))->count()),
        ];
    }
}
