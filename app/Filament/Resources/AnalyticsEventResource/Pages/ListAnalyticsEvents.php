<?php

declare (strict_types=1);
namespace App\Filament\Resources\AnalyticsEventResource\Pages;

use App\Filament\Resources\AnalyticsEventResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
/**
 * ListAnalyticsEvents
 * 
 * Filament v4 resource for ListAnalyticsEvents management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class ListAnalyticsEvents extends ListRecords
{
    protected static string $resource = AnalyticsEventResource::class;
    /**
     * Handle getHeaderActions functionality with proper error handling.
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [Actions\Action::make('back_to_dashboard')->label(__('common.back_to_dashboard'))->icon('heroicon-o-arrow-left')->color('gray')->url('/admin')->tooltip(__('common.back_to_dashboard_tooltip')), Actions\CreateAction::make()];
    }
}