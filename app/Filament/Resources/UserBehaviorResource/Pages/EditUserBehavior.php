<?php declare(strict_types=1);

namespace App\Filament\Resources\UserBehaviorResource\Pages;

use App\Filament\Resources\UserBehaviorResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

final class EditUserBehavior extends EditRecord
{
    protected static string $resource = UserBehaviorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

