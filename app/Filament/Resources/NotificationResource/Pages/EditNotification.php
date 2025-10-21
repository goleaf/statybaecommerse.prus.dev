<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditNotification extends EditRecord
{
    protected static string $resource = NotificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->syncReadState($data);
    }

    private function syncReadState(array $data): array
    {
        $isRead = (bool) ($data['is_read'] ?? false);

        if ($isRead) {
            $data['read_at'] = $data['read_at'] ?? now();
        } else {
            $data['read_at'] = null;
            $data['is_read'] = false;
        }

        return $data;
    }
}
