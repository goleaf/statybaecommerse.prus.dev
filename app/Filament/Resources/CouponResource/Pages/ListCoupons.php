<?php

declare(strict_types=1);

namespace App\Filament\Resources\CouponResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\CouponResource;
use Filament\Actions;

final class ListCoupons extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = CouponResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
