<?php

declare(strict_types=1);

namespace App\Filament\Resources\AdminUserResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\AdminUserResource;
use Filament\Actions;

class ListAdminUsers extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = AdminUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
