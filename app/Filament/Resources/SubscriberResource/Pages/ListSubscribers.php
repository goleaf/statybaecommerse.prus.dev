<?php

declare(strict_types=1);

namespace App\Filament\Resources\SubscriberResource\Pages;

use App\Filament\Resources\SubscriberResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

final class ListSubscribers extends ListRecords
{
    protected static string $resource = SubscriberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('subscribers.tabs.all')),
            
            'active' => Tab::make(__('subscribers.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_active', true)->count()),
            
            'verified' => Tab::make(__('subscribers.tabs.verified'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_verified', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_verified', true)->count()),
            
            'unverified' => Tab::make(__('subscribers.tabs.unverified'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_verified', false))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_verified', false)->count()),
            
            'newsletter' => Tab::make(__('subscribers.tabs.newsletter'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'newsletter'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'newsletter')->count()),
            
            'promotions' => Tab::make(__('subscribers.tabs.promotions'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'promotions'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'promotions')->count()),
            
            'updates' => Tab::make(__('subscribers.tabs.updates'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'updates'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'updates')->count()),
            
            'today' => Tab::make(__('subscribers.tabs.today'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('created_at', today()))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->whereDate('created_at', today())->count()),
            
            'this_week' => Tab::make(__('subscribers.tabs.this_week'))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count()),
        ];
    }
}
