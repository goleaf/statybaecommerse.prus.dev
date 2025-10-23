<?php

declare(strict_types=1);

namespace App\Filament\Resources\CouponUsageResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\CouponUsageResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

class ListCouponUsages extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = CouponUsageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
