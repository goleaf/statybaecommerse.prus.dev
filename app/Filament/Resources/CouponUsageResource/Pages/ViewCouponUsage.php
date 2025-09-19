<?php declare(strict_types=1);

namespace App\Filament\Resources\CouponUsageResource\Pages;

use App\Filament\Resources\CouponUsageResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;

class ViewCouponUsage extends ViewRecord
{
    protected static string $resource = CouponUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}

