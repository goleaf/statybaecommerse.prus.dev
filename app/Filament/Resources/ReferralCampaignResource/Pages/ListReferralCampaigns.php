<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCampaignResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\ReferralCampaignResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

final class ListReferralCampaigns extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = ReferralCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(), // Provide a quick language toggle for the grid view.
            Actions\CreateAction::make(),
        ];
    }
}
