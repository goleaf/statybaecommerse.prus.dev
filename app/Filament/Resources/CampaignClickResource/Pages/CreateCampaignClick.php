<?php

declare (strict_types=1);
namespace App\Filament\Resources\CampaignClickResource\Pages;

use App\Filament\Resources\CampaignClickResource;
use Filament\Resources\Pages\CreateRecord;
/**
 * CreateCampaignClick
 * 
 * Filament v4 resource for CreateCampaignClick management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
class CreateCampaignClick extends CreateRecord
{
    protected static string $resource = CampaignClickResource::class;
    /**
     * Handle getRedirectUrl functionality with proper error handling.
     * @return string
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}