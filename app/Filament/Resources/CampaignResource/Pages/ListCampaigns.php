<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignResource\Pages;

use App\Filament\Resources\CampaignResource;
use Filament\Actions;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Resources\Pages\ListRecords;
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
            'all' => Tab::make($this->label('campaigns.tabs.all', 'All')),
            'active' => Tab::make($this->label('campaigns.tabs.active', 'Active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'active'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('status', 'active')->count()),
            'scheduled' => Tab::make($this->label('campaigns.tabs.scheduled', 'Scheduled'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'scheduled'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('status', 'scheduled')->count()),
            'draft' => Tab::make($this->label('campaigns.tabs.draft', 'Draft'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'draft'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('status', 'draft')->count()),
            'paused' => Tab::make($this->label('campaigns.tabs.paused', 'Paused'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'paused'))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('status', 'paused')->count()),
            'inactive' => Tab::make($this->label('campaigns.tabs.inactive', 'Inactive'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_active', false)->count()),
            'featured' => Tab::make($this->label('campaigns.tabs.featured', 'Featured'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_featured', true))
                ->badge(fn () => $this->getResource()::getEloquentQuery()->where('is_featured', true)->count()),
        ];
    }

    private function label(string $key, string $fallback): string
    {
        $translated = __($key);

        return $translated === $key ? $fallback : $translated;
    }
}
