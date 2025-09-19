<?php declare(strict_types=1);

namespace App\Filament\Resources\CampaignCustomerSegmentResource\Pages;

use App\Filament\Resources\CampaignCustomerSegmentResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditCampaignCustomerSegment extends EditRecord
{
    protected static string $resource = CampaignCustomerSegmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

