<?php

declare(strict_types=1);

namespace App\Filament\Resources\CampaignProductTargetResource\Pages;

use App\Filament\Resources\CampaignProductTargetResource;
use Filament\Resources\Pages\ViewRecord;

/**
 * ViewCampaignProductTarget renders a read-only summary of campaign targeting.
 */
final class ViewCampaignProductTarget extends ViewRecord
{
    /**
     * @var class-string<\Filament\Resources\Resource> The associated resource class.
     */
    protected static string $resource = CampaignProductTargetResource::class;
}
