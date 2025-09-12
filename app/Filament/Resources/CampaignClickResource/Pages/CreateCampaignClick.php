<?php declare(strict_types=1);

namespace App\Filament\Resources\CampaignClickResource\Pages;

use App\Filament\Resources\CampaignClickResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCampaignClick extends CreateRecord
{
    protected static string $resource = CampaignClickResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
