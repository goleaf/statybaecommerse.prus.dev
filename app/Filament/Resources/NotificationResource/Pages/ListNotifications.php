<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\NotificationResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

final class ListNotifications extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = NotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
