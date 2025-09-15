<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignResource\Pages;

use App\Filament\Resources\CampaignResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

final class ListCampaigns extends ListRecords
{
    protected static string $resource = CampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('campaigns.tabs.all')),
            
            'active' => Tab::make(__('campaigns.tabs.active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_active', true)->count()),
            
            'scheduled' => Tab::make(__('campaigns.tabs.scheduled'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_scheduled', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_scheduled', true)->count()),
            
            'running' => Tab::make(__('campaigns.tabs.running'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'running'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('status', 'running')->count()),
            
            'paused' => Tab::make(__('campaigns.tabs.paused'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'paused'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('status', 'paused')->count()),
            
            'completed' => Tab::make(__('campaigns.tabs.completed'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'completed'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('status', 'completed')->count()),
            
            'email' => Tab::make(__('campaigns.tabs.email'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'email'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'email')->count()),
            
            'sms' => Tab::make(__('campaigns.tabs.sms'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'sms'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'sms')->count()),
            
            'push' => Tab::make(__('campaigns.tabs.push'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', 'push'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('type', 'push')->count()),
        ];
    }
}
