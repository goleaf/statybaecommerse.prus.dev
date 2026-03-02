<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountRedemptionResource\Pages;

use App\Filament\Resources\DiscountRedemptionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDiscountRedemption extends CreateRecord
{
    protected static string $resource = DiscountRedemptionResource::class;

    /**
     * @param  array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! is_numeric($data['user_id'] ?? null)) {
            $requestedUserId = request()->integer('user_id');

            if ($requestedUserId > 0) {
                $data['user_id'] = $requestedUserId;
            }
        }

        return DiscountRedemptionResource::normalizePayload($data);
    }

    protected function getRedirectUrl(): string
    {
        $redirectUrl = request()->query('redirect');

        if (is_string($redirectUrl) && $redirectUrl !== '') {
            return $redirectUrl;
        }

        return parent::getRedirectUrl();
    }
}
