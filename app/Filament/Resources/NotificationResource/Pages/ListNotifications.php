<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\NotificationResource;
use Filament\Actions;

final class ListNotifications extends BaseListRecords
{
    protected static string $resource = NotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
