<?php

declare (strict_types=1);
namespace App\Filament\Resources\CampaignConversionResource\Pages;

use App\Filament\Resources\CampaignConversionResource;
use Filament\Resources\Pages\CreateRecord;
/**
 * CreateCampaignConversion
 * 
 * Filament v4 resource for CreateCampaignConversion management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string $resource
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
class CreateCampaignConversion extends CreateRecord
{
    protected static string $resource = CampaignConversionResource::class;
    /**
     * Handle getRedirectUrl functionality with proper error handling.
     * @return string
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}