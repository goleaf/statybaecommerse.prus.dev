<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountRedemptionResource\Pages;

use App\Filament\Resources\DiscountRedemptionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDiscountRedemption extends CreateRecord
{
    protected static string $resource = DiscountRedemptionResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return DiscountRedemptionResource::normalizePayload($data);
    }
}
