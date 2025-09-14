<?php

declare (strict_types=1);
namespace App\Filament\Resources\CompanyResource\Pages;

use App\Filament\Resources\CompanyResource;
use App\Filament\Resources\CompanyResource\Widgets\CompanyStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
/**
 * ListCompanies
 * 
 * Filament v4 resource for ListCompanies management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class ListCompanies extends ListRecords
{
    protected static string $resource = CompanyResource::class;
    /**
     * Handle getHeaderActions functionality with proper error handling.
     * @return array
     */
    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('Add Company')->icon('heroicon-o-plus')];
    }
    /**
     * Handle getHeaderWidgets functionality with proper error handling.
     * @return array
     */
    protected function getHeaderWidgets(): array
    {
        return [CompanyStatsWidget::class];
    }
}