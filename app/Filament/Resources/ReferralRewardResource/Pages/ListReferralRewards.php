<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralRewardResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\ReferralRewardResource;
use Filament\Actions;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable;

final class ListReferralRewards extends BaseListRecords
{
    use Translatable;

    protected static string $resource = ReferralRewardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            Actions\CreateAction::make(),
        ];
    }
}
