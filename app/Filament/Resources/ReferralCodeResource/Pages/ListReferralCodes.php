<?php declare(strict_types=1);

namespace App\Filament\Resources\ReferralCodeResource\Pages;

use App\Filament\Resources\ReferralCodeResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

final class ListReferralCodes extends ListRecords
{
    protected static string $resource = ReferralCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

