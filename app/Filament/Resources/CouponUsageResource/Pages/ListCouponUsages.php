<?php

declare(strict_types=1);

namespace App\Filament\Resources\CouponUsageResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\CouponUsageResource;
use Filament\Actions;

class ListCouponUsages extends BaseListRecords
{
    protected static string $resource = CouponUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
