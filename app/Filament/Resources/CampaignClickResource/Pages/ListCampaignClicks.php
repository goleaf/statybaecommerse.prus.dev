<?php

declare (strict_types=1);
namespace App\Filament\Resources\CampaignClickResource\Pages;

use App\Filament\Resources\CampaignClickResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
/**
 * ListCampaignClicks
 * 
 * Filament v4 resource for ListCampaignClicks management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
class ListCampaignClicks extends ListRecords
{
    protected static string $resource = CampaignClickResource::class;
    /**
     * Handle getHeaderActions functionality with proper error handling.
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make(), Actions\ExportAction::make()->exporter(\Filament\Tables\Exports\ExcelExport::class)->fileName('campaign_clicks_export')];
    }
    /**
     * Handle getTabs functionality with proper error handling.
     * @return array
     */
    public function getTabs(): array
    {
        return ['all' => Tab::make(__('campaign_clicks.all_clicks'))->icon('heroicon-o-cursor-arrow-rays'), 'converted' => Tab::make(__('campaign_clicks.converted_clicks'))->icon('heroicon-o-check-circle')->modifyQueryUsing(fn(Builder $query) => $query->where('is_converted', true))->badge(fn() => $this->getModel()::where('is_converted', true)->count()), 'recent' => Tab::make(__('campaign_clicks.recent_clicks'))->icon('heroicon-o-clock')->modifyQueryUsing(fn(Builder $query) => $query->where('clicked_at', '>=', now()->subDays(7)))->badge(fn() => $this->getModel()::where('clicked_at', '>=', now()->subDays(7))->count()), 'cta' => Tab::make(__('campaign_clicks.cta_clicks'))->icon('heroicon-o-cursor-arrow-click')->modifyQueryUsing(fn(Builder $query) => $query->where('click_type', 'cta'))->badge(fn() => $this->getModel()::where('click_type', 'cta')->count()), 'banner' => Tab::make(__('campaign_clicks.banner_clicks'))->icon('heroicon-o-photo')->modifyQueryUsing(fn(Builder $query) => $query->where('click_type', 'banner'))->badge(fn() => $this->getModel()::where('click_type', 'banner')->count())];
    }
}