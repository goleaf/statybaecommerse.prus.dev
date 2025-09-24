<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralStatisticsResource\Pages;

use App\Filament\Resources\ReferralStatisticsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditReferralStatistics extends EditRecord
{
    protected static string $resource = ReferralStatisticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
