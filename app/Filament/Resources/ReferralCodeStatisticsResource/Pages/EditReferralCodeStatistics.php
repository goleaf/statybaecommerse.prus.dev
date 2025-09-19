<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCodeStatisticsResource\Pages;

use App\Filament\Resources\ReferralCodeStatisticsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

final class EditReferralCodeStatistics extends EditRecord
{
    protected static string $resource = ReferralCodeStatisticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
