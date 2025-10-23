<?php

declare(strict_types=1);

namespace App\Filament\Resources\CouponResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\CouponResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

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
