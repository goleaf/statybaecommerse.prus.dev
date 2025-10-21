<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralRewardResource\Pages;

use App\Filament\Resources\ReferralRewardResource;
use Filament\Resources\Pages\CreateRecord;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\CreateRecord\Concerns\Translatable;

final class CreateReferralReward extends CreateRecord
{
    use Translatable;

    protected static string $resource = ReferralRewardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
        ];
    }
}
