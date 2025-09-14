<?php

declare (strict_types=1);
namespace App\Filament\Resources\CampaignConversionResource\Pages;

use App\Filament\Resources\CampaignConversionResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
/**
 * ListCampaignConversions
 * 
 * Filament v4 resource for ListCampaignConversions management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
class ListCampaignConversions extends ListRecords
{
    protected static string $resource = CampaignConversionResource::class;
    /**
     * Handle getHeaderActions functionality with proper error handling.
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make(), Actions\Action::make('export')->label(__('campaign_conversions.actions.export_all'))->icon('heroicon-o-arrow-down-tray')->color('info')->action(function () {
            // Export logic here
        })];
    }
    /**
     * Handle getTabs functionality with proper error handling.
     * @return array
     */
    public function getTabs(): array
    {
        return ['all' => Tab::make(__('campaign_conversions.tabs.all'))->badge(fn() => $this->getModel()::count()), 'completed' => Tab::make(__('campaign_conversions.tabs.completed'))->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'completed'))->badge(fn() => $this->getModel()::where('status', 'completed')->count()), 'pending' => Tab::make(__('campaign_conversions.tabs.pending'))->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'pending'))->badge(fn() => $this->getModel()::where('status', 'pending')->count()), 'high_value' => Tab::make(__('campaign_conversions.tabs.high_value'))->modifyQueryUsing(fn(Builder $query) => $query->where('conversion_value', '>=', 100))->badge(fn() => $this->getModel()::where('conversion_value', '>=', 100)->count()), 'recent' => Tab::make(__('campaign_conversions.tabs.recent'))->modifyQueryUsing(fn(Builder $query) => $query->where('converted_at', '>=', now()->subDays(7)))->badge(fn() => $this->getModel()::where('converted_at', '>=', now()->subDays(7))->count()), 'mobile' => Tab::make(__('campaign_conversions.tabs.mobile'))->modifyQueryUsing(fn(Builder $query) => $query->where('device_type', 'mobile'))->badge(fn() => $this->getModel()::where('device_type', 'mobile')->count()), 'desktop' => Tab::make(__('campaign_conversions.tabs.desktop'))->modifyQueryUsing(fn(Builder $query) => $query->where('device_type', 'desktop'))->badge(fn() => $this->getModel()::where('device_type', 'desktop')->count())];
    }
}