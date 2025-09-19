<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserBehaviorResource\Pages;

use App\Filament\Resources\UserBehaviorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListUserBehaviors extends ListRecords
{
    protected static string $resource = UserBehaviorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
