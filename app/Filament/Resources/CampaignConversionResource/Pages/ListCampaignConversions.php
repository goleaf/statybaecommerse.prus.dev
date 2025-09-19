<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignConversionResource\Pages;

use App\Filament\Resources\CampaignConversionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListCampaignConversions extends ListRecords
{
    protected static string $resource = CampaignConversionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
