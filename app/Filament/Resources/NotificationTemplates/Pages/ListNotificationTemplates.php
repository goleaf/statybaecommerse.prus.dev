<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificationTemplates\Pages;

use App\Filament\Pages\Support\BaseListRecords;
use App\Filament\Resources\NotificationTemplates\NotificationTemplateResource;
use Filament\Actions\CreateAction;

class ListNotificationTemplates extends BaseListRecords
{
    protected static string $resource = NotificationTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
