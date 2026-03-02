<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralRewards\Pages;

use App\Filament\Resources\ReferralRewards\ReferralRewardResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReferralReward extends CreateRecord
{
    protected static string $resource = ReferralRewardResource::class;

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

        return $data;
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
