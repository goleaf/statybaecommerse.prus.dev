<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\CampaignResource;
use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;

final class ListCampaigns extends BaseListRecords
{
        use HasWidgetTabs;

    protected static string $resource = CampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getWidgetTabs(): array
    {
        return [
            'all'    => SchemaTab::make($this->label('campaigns.tabs.all', 'All')),
            'active' => SchemaTab::make($this->label('campaigns.tabs.active', 'Active'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'active'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('status', 'active')->count()),
            'scheduled' => WidgetTab::make($this->label('campaigns.tabs.scheduled', 'Scheduled'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'scheduled'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('status', 'scheduled')->count()),
            'draft' => WidgetTab::make($this->label('campaigns.tabs.draft', 'Draft'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'draft'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('status', 'draft')->count()),
            'paused' => WidgetTab::make($this->label('campaigns.tabs.paused', 'Paused'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'paused'))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('status', 'paused')->count()),
            'inactive' => WidgetTab::make($this->label('campaigns.tabs.inactive', 'Inactive'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('is_active', false)->count()),
            'featured' => WidgetTab::make($this->label('campaigns.tabs.featured', 'Featured'))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_featured', true))
                ->value(fn () => $this->getResource()::getEloquentQuery()->where('is_featured', true)->count()),
        ];
    }

    private function label(string $key, string $fallback): string
    {
        $translated = __($key);

        return $translated === $key ? $fallback : $translated;
    }
}
