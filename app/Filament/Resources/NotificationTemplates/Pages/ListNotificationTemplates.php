<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificationTemplates\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\NotificationTemplates\NotificationTemplateResource;
use Filament\Actions\CreateAction;
use App\Filament\Pages\Support\BaseListRecords;

class ListNotificationTemplates extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = NotificationTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
