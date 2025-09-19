<?php declare(strict_types=1);

namespace App\Filament\Resources\AdminUserResource\Pages;

use App\Filament\Resources\AdminUserResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditAdminUser extends EditRecord
{
    protected static string $resource = AdminUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}

