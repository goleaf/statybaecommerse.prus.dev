<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificationTemplateResource\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\NotificationTemplateResource;
use Filament\Actions;

final class ListNotificationTemplates extends BaseListRecords
{
    protected static string $resource = NotificationTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
