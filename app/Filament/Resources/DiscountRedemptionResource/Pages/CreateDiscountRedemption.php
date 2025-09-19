<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountRedemptionResource\Pages;

use App\Filament\Resources\DiscountRedemptionResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateDiscountRedemption extends CreateRecord
{
    protected static string $resource = DiscountRedemptionResource::class;
}
