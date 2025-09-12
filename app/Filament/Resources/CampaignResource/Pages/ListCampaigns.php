<?php declare(strict_types=1);

namespace App\Filament\Resources\CampaignResource\Pages;

use App\Filament\Resources\CampaignResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;

final class ListCampaigns extends ListRecords
{
    protected static string $resource = CampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back_to_dashboard')
                ->label(__('common.back_to_dashboard'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url('/admin')
                ->tooltip(__('common.back_to_dashboard_tooltip')),
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('campaigns.tabs.all'))
                ->icon('heroicon-o-megaphone'),
            'active' => Tab::make(__('campaigns.tabs.active'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'active'))
                ->icon('heroicon-o-play')
                ->badge(fn() => \App\Models\Campaign::where('status', 'active')->count()),
            'scheduled' => Tab::make(__('campaigns.tabs.scheduled'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'scheduled'))
                ->icon('heroicon-o-clock')
                ->badge(fn() => \App\Models\Campaign::where('status', 'scheduled')->count()),
            'draft' => Tab::make(__('campaigns.tabs.draft'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'draft'))
                ->icon('heroicon-o-document-text')
                ->badge(fn() => \App\Models\Campaign::where('status', 'draft')->count()),
            'paused' => Tab::make(__('campaigns.tabs.paused'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'paused'))
                ->icon('heroicon-o-pause')
                ->badge(fn() => \App\Models\Campaign::where('status', 'paused')->count()),
            'completed' => Tab::make(__('campaigns.tabs.completed'))
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'completed'))
                ->icon('heroicon-o-check-circle')
                ->badge(fn() => \App\Models\Campaign::where('status', 'completed')->count()),
        ];
    }
}
