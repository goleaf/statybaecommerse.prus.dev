<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserBehaviorResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\UserBehaviorResource;
use Filament\Actions;

final class ListUserBehaviors extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = UserBehaviorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
