<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralRewardResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\ReferralRewardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListReferralRewards extends ListRecords
{
    use HasResizableColumns;

    protected static string $resource = ReferralRewardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
