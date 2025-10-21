<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\ReferralResource;
use Filament\Actions;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable as TranslatableListRecords;

final class ListReferrals extends BaseListRecords
{
    use TranslatableListRecords;

    protected static string $resource = ReferralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            Actions\CreateAction::make(),
        ];
    }
}
