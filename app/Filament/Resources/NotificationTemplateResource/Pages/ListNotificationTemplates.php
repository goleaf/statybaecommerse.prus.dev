<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificationTemplateResource\Pages;

use App\Filament\Concerns\HasResizableColumns;
use App\Filament\Resources\NotificationTemplateResource;
use Filament\Actions;
use App\Filament\Pages\Support\BaseListRecords;

final class ListNotificationTemplates extends BaseListRecords
{
    use HasResizableColumns;

    protected static string $resource = NotificationTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
