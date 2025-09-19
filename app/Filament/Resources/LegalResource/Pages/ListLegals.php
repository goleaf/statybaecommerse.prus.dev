<?php

declare(strict_types=1);

namespace App\Filament\Resources\LegalResource\Pages;

use App\Filament\Resources\LegalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

final class ListLegals extends ListRecords
{
    protected static string $resource = LegalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('legals.tabs.all')),
            
            'active' => Tab::make(__('legals.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_active', true)->count()),
            
            'terms' => Tab::make(__('legals.tabs.terms'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'terms'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'terms')->count()),
            
            'privacy' => Tab::make(__('legals.tabs.privacy'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'privacy'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'privacy')->count()),
            
            'cookies' => Tab::make(__('legals.tabs.cookies'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'cookies'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'cookies')->count()),
            
            'gdpr' => Tab::make(__('legals.tabs.gdpr'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'gdpr'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'gdpr')->count()),
            
            'shipping' => Tab::make(__('legals.tabs.shipping'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'shipping'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'shipping')->count()),
            
            'returns' => Tab::make(__('legals.tabs.returns'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'returns'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'returns')->count()),
            
            'current' => Tab::make(__('legals.tabs.current'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('valid_from', '<=', now())->where(function ($q) {
                    $q->whereNull('valid_until')->orWhere('valid_until', '>=', now());
                }))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('valid_from', '<=', now())->where(function ($q) {
                    $q->whereNull('valid_until')->orWhere('valid_until', '>=', now());
                })->count()),
        ];
    }
}
