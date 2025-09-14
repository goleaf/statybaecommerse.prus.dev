<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignConversionResource\Pages;

use App\Filament\Resources\CampaignConversionResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * CreateCampaignConversion
 * 
 * Filament resource for admin panel management.
 */
class CreateCampaignConversion extends CreateRecord
{
    protected static string $resource = CampaignConversionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
